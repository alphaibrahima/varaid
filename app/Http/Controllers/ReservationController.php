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

        return view('reservation.index', compact('slotCounts', 'userAssociation', 'user', 'affiliationVerified'));
    }

    public function getSlots($date)
    {
        // Vérifier si la date est valide
        if (!strtotime($date)) {
            return response()->json(['error' => 'Date invalide'], 400);
        }
    
        // Récupérer les créneaux disponibles pour cette date avec le nombre de places restantes
        $slots = Slot::where('date', $date)
            ->where('available', true)
            ->orderBy('start_time')
            ->get()
            ->map(function($slot) {
                // Calculer le nombre de réservations déjà effectuées pour ce créneau
                $reservedCount = Reservation::where('slot_id', $slot->id)
                    ->where('status', '!=', 'canceled')
                    ->sum('quantity');
                
                // Ajouter la propriété places_restantes
                $slot->places_restantes = max(0, $slot->max_reservations - $reservedCount);
                
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

    public function confirmReservation(Request $request)
    {
        try {
            // Récupérer les données JSON brutes
            $rawData = json_decode($request->getContent(), true);
            \Log::debug('Données JSON brutes:', $rawData);
            
            // Vérifier l'authentification
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Créer manuellement un tableau avec la bonne structure
            $data = [
                'slot_id' => $rawData['slot_id'] ?? $rawData['slotId'] ?? null,
                'quantity' => $rawData['quantity'] ?? null,
                'reservation_number' => $rawData['reservation_number'] ?? $rawData['reservationNumber'] ?? null,
                'payment_intent_id' => $rawData['payment_intent_id'] ?? $rawData['paymentIntentId'] ?? null,
                'skip_selection' => $rawData['skip_selection'] ?? $rawData['skipSelection'] ?? false,
                'owners' => $rawData['owners'] ?? []
            ];
            
            // Validation des données essentielles
            if (!$data['slot_id'] || !$data['quantity'] || !$data['payment_intent_id']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Données de réservation incomplètes'
                ], 422);
            }
            
            // Vérifier si le créneau existe
            $slot = Slot::find($data['slot_id']);
            if (!$slot) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Créneau non trouvé'
                ], 404);
            }
            
            // Créer la réservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'slot_id' => $data['slot_id'],
                'association_id' => $user->association_id,
                'size' => 'grand', // Valeur par défaut
                'quantity' => $data['quantity'],
                'code' => $data['reservation_number'],
                'status' => 'confirmed',
                'date' => now(),
                'skip_selection' => $data['skip_selection'],
                'owners_data' => json_encode($data['owners']),
                'payment_intent_id' => $data['payment_intent_id']
            ]);
            
            // Retourner une réponse de succès
            return response()->json([
                'status' => 'success',
                'message' => 'Réservation confirmée avec succès',
                'data' => $reservation,
                'redirectUrl' => route('reservation.receipt', ['code' => $reservation->code])
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur de confirmation:', [
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
}