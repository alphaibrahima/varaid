<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;
use App\Notifications\AffiliationCodeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\ReservationConfirmation;

class ReservationController extends Controller
{
    public function index()
    {
        $slotCounts = Slot::select('date', DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    
        $user = Auth::user();
        $userAssociation = $user->association ? $user->association->name : 'N/A';
        
        // Vérifier si l'utilisateur a vérifié son affiliation
        $affiliationVerified = $user->role !== 'buyer' || $user->hasVerifiedAffiliation();
        
        // Obtenir le nombre d'agneaux déjà réservés par l'utilisateur
        $userReservationsCount = $this->getUserReservationsCount();
        
        // Calculer le nombre d'agneaux restants que l'utilisateur peut réserver
        $remainingReservations = max(0, 4 - $userReservationsCount);
    
        return view('reservation.index', compact(
            'slotCounts', 
            'userAssociation', 
            'user', 
            'affiliationVerified',
            'userReservationsCount',
            'remainingReservations'
        ));
    }

    public function getSlots($date)
    {
        // Vérifier si la date est valide
        if (!strtotime($date)) {
            return response()->json(['error' => 'Date invalide'], 400);
        }
    
        // Récupérer TOUS les créneaux pour cette date (y compris les bloqués)
        $slots = Slot::where('date', $date)
            ->orderBy('start_time')
            ->get()
            ->map(function($slot) {
                // Calculer le nombre de réservations déjà effectuées pour ce créneau
                $reservedCount = Reservation::where('slot_id', $slot->id)
                    ->where('status', '!=', 'canceled')
                    ->sum('quantity');
                
                // Ajouter la propriété places_restantes
                $slot->places_restantes = $slot->available ? max(0, $slot->max_reservations - $reservedCount) : 0;
                
                return $slot;
            });
    
        return response()->json($slots);
    }

    private function getAvailabilityStatus($totalCapacity)
    {
        return match(true) {
            $totalCapacity === 0 => 'complet',
            $totalCapacity < 5 => 'presque_complet',
            default => 'disponible'
        };
    }

// Modifier la méthode confirmReservation
public function confirmReservation(Request $request)
{
    try {
        Log::info('Reservation request received:', $request->all());

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        // Validate the request - mise à jour sans payment_intent_id
        $validated = $request->validate([
            'reservation_number' => 'required|string',
            'slot_id' => 'required|integer|exists:slots,id',
            'quantity' => 'required|integer|min:1|max:4',
            'skip_selection' => 'boolean',
            'owners' => 'array'
        ]);

        // Vérifier la limite globale de 4 agneaux par utilisateur
        $userReservationsCount = $this->getUserReservationsCount();
        $newTotalCount = $userReservationsCount + $validated['quantity'];
        
        if ($newTotalCount > 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas réserver plus de 4 agneaux au total. Vous avez déjà réservé ' . $userReservationsCount . ' agneau(x).',
                'limitReached' => true,
                'currentCount' => $userReservationsCount
            ], 422);
        }

        // Vérifier si le créneau a assez de capacité
        $slot = Slot::findOrFail($validated['slot_id']);
        $currentReservations = Reservation::where('slot_id', $slot->id)->sum('quantity');
        
        if (($currentReservations + $validated['quantity']) > $slot->max_reservations) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plus assez de places disponibles pour ce créneau'
            ], 422);
        }

        // Traiter et stocker les données des propriétaires
        $ownersData = $request->input('owners');
        if (!empty($ownersData)) {
            // Encodage JSON pour stockage
            $encodedOwnersData = json_encode($ownersData);
            Log::info('Owners data encoded:', ['data' => $encodedOwnersData]);
        } else {
            $encodedOwnersData = null;
        }

        // Création de la réservation sans payment_intent_id
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'slot_id' => $validated['slot_id'],
            'association_id' => $user->association_id,
            'size' => 'grand', // Valeur par défaut
            'quantity' => $validated['quantity'],
            'code' => $validated['reservation_number'],
            'status' => 'confirmed', // Directement confirmé car acompte payé en amont
            'date' => $slot->date, // Utiliser la date du créneau
            'skip_selection' => $request->input('skip_selection', false),
            'owners_data' => $encodedOwnersData,
            'payment_intent_id' => 'payé-en-personne-' . time() // Simuler un identifiant 
        ]);

        // Envoyer une notification de confirmation
        if ($reservation) {
            Log::info('Sending confirmation notification to user', ['user_id' => $user->id, 'reservation_id' => $reservation->id]);
            try {
                $user->notify(new ReservationConfirmation($reservation));
            } catch (\Exception $e) {
                Log::error('Error sending notification', ['error' => $e->getMessage()]);
            }
        }

        // Return success response with redirect URL
        return response()->json([
            'status' => 'success',
            'message' => 'Réservation créée avec succès',
            'data' => $reservation,
            'redirectUrl' => route('reservation.receipt', ['code' => $reservation->code])
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error:', [
            'message' => $e->getMessage(),
            'errors' => $e->errors()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Reservation error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
    

    public function showReceipt($code)
    {
        $reservation = Reservation::with(['user', 'slot', 'association'])
            ->where('code', $code)
            ->firstOrFail();

        // Convert date string to Carbon instance if it's not already
        $reservation->date = $reservation->date instanceof Carbon 
            ? $reservation->date 
            : Carbon::parse($reservation->date);

        return view('reservation.receipt', compact('reservation'));
    }

    public function downloadReceipt($code)
    {
        $reservation = Reservation::with(['user', 'slot', 'association'])
            ->where('code', $code)
            ->firstOrFail();
    
        $pdf = PDF::loadView('reservation.receipt-pdf', compact('reservation'));
        
        return $pdf->download('recu-reservation-' . $code . '.pdf');
    }

    // Méthode pour vérifier le code d'affiliation via AJAX
    public function verifyAffiliationCode(Request $request)
    {
        $request->validate([
            'affiliation_code' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        if ($user->affiliation_code === strtoupper($request->affiliation_code)) {
            $user->markAffiliationAsVerified();
            
            // Journaliser la vérification
            \Log::info('Affiliation vérifiée', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $user->affiliation_code
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Affiliation vérifiée avec succès!'
            ]);
        }
        
        // Journaliser la tentative échouée
        \Log::warning('Échec de vérification d\'affiliation', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempt' => $request->affiliation_code,
            'expected' => $user->affiliation_code
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Le code d\'affiliation est incorrect.'
        ], 422);
    }
    
    public function resendAffiliationCode(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        // Régénérer le code si nécessaire
        if (!$user->affiliation_code) {
            $user->generateAffiliationCode();
        }
        
        // Envoyer la notification
        $user->notify(new AffiliationCodeNotification());
        
        // Journaliser l'envoi
        \Log::info('Code d\'affiliation renvoyé', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Code d\'affiliation envoyé!'
        ]);
    }
    
    // Middleware pour vérifier l'affiliation - peut être utilisé si nécessaire
    public function checkAffiliation(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'buyer' && !$user->hasVerifiedAffiliation()) {
            return response()->json([
                'verified' => false,
                'message' => 'Affiliation non vérifiée',
                'needsVerification' => true
            ]);
        }
        
        return response()->json([
            'verified' => true,
            'message' => 'Affiliation vérifiée',
            'needsVerification' => false
        ]);
    }

    /**
     * Récupère le nombre total d'agneaux déjà réservés par l'utilisateur
     */
    private function getUserReservationsCount()
    {
        $userId = Auth::id();
        return Reservation::where('user_id', $userId)
            ->where('status', '!=', 'canceled')  // Ne pas compter les réservations annulées
            ->sum('quantity');
    }

    /**
     * Vérifie la limite de réservation pour l'utilisateur
     */
    public function checkReservationLimit()
    {
        $userReservationsCount = $this->getUserReservationsCount();
        $remainingReservations = max(0, 4 - $userReservationsCount);
        
        return response()->json([
            'currentCount' => $userReservationsCount,
            'remainingCount' => $remainingReservations,
            'limitReached' => $userReservationsCount >= 4
        ]);
    }


}