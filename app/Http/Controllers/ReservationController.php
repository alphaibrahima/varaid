<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\ReservationConfirmation;

class ReservationController extends Controller
{
    public function index()
    {
        // Récupérer uniquement les jours qui ont au moins un créneau disponible
        $slotCounts = Slot::select('date', DB::raw('count(*) as total'))
            ->where('available', true) // Ajouter cette condition
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    
        $user = Auth::user();
        $userAssociation = $user->association ? $user->association->name : 'N/A';
        
        // Obtenir le nombre d'agneaux déjà réservés par l'utilisateur (seulement pertinent pour les acheteurs)
        $userReservationsCount = $this->getUserReservationsCount();
        
        // Calculer le nombre d'agneaux restants que l'utilisateur peut réserver
        // (infini pour les associations, 4 - réservations actuelles pour les acheteurs)
        $remainingReservations = $user->role === 'association' ? PHP_INT_MAX : max(0, 4 - $userReservationsCount);
    
        return view('reservation.index', compact(
            'slotCounts', 
            'userAssociation', 
            'user', 
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

        // Récupérer seulement les créneaux disponibles pour cette date
        $slots = Slot::where('date', $date)
            ->where('available', true) // Ajout de cette condition pour filtrer les créneaux bloqués
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

            // Validate the request
            $validated = $request->validate([
                'reservation_number' => 'required|string',
                'slot_id' => 'required|integer|exists:slots,id',
                'quantity' => 'required|integer|min:1',
                'skip_selection' => 'boolean',
                'owners' => 'array'
            ]);

            // Vérifier la limite globale de 4 agneaux uniquement pour les acheteurs
            if ($user->role === 'buyer') {
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

            // Création de la réservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'slot_id' => $validated['slot_id'],
                'association_id' => $user->role === 'association' ? $user->id : $user->association_id,
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
                    
                    // Envoyer un SMS si le service est configuré
                    $this->sendConfirmationSMS($user->phone, $reservation);
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
        $user = Auth::user();
        $userReservationsCount = $this->getUserReservationsCount();
        
        // Pour les associations, pas de limite
        if ($user->role === 'association') {
            return response()->json([
                'currentCount' => $userReservationsCount,
                'remainingCount' => PHP_INT_MAX, // Valeur très élevée pour signifier "pas de limite"
                'limitReached' => false,
                'isAssociation' => true
            ]);
        }
        
        // Pour les acheteurs, limite de 4
        $remainingReservations = max(0, 4 - $userReservationsCount);
        
        return response()->json([
            'currentCount' => $userReservationsCount,
            'remainingCount' => $remainingReservations,
            'limitReached' => $userReservationsCount >= 4,
            'isAssociation' => false
        ]);
    }
    
    /**
     * Envoie un SMS de confirmation de réservation
     */
    private function sendConfirmationSMS($phoneNumber, $reservation)
    {
        try {
            // Vérifier si la configuration SMS est disponible
            $smsConfig = config('services.sms');
            
            if (!$smsConfig || !isset($smsConfig['enabled']) || !$smsConfig['enabled']) {
                Log::info('Service SMS non configuré ou désactivé');
                return false;
            }
            
            // Vérifier que le fournisseur est bien Brevo
            if ($smsConfig['provider'] === 'brevo') {
                $apiKey = $smsConfig['brevo']['api_key'];
                $sender = $smsConfig['brevo']['sender'];
                
                if (!$apiKey || !$sender) {
                    Log::warning('Configuration Brevo incomplète');
                    return false;
                }
                
                // Formater la date et l'heure
                $date = $reservation->date->format('d/m/Y');
                $time = is_string($reservation->slot->start_time) 
                    ? substr($reservation->slot->start_time, 0, 5) 
                    : $reservation->slot->start_time->format('H:i');
                
                // Nettoyer le numéro de téléphone (enlever les espaces, les +, etc.)
                $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);
                if (substr($phoneNumber, 0, 1) !== '+') {
                    // Ajouter le préfixe international si nécessaire (français par défaut)
                    if (substr($phoneNumber, 0, 1) === '0') {
                        $phoneNumber = '+33' . substr($phoneNumber, 1);
                    } else {
                        // Si le numéro ne commence pas par 0, ajouter simplement un +
                        $phoneNumber = '+' . $phoneNumber;
                    }
                }
                
                // Créer le message
                $message = "Varaïd: Votre réservation d'agneau (code: {$reservation->code}) est confirmée! ";
                $message .= "Rendez-vous le {$date} à {$time}. ";
                $message .= "Reçu disponible sur votre compte.";
                
                // Préparer la requête pour l'API Brevo
                $url = 'https://api.brevo.com/v3/transactionalSMS/sms';
                $data = [
                    'sender' => $sender,
                    'recipient' => $phoneNumber,
                    'content' => $message,
                    'type' => 'transactional'
                ];
                
                // Initier la requête curl
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => [
                        'accept: application/json',
                        'api-key: ' . $apiKey,
                        'content-type: application/json'
                    ],
                ]);
                
                $response = curl_exec($curl);
                $err = curl_error($curl);
                
                curl_close($curl);
                
                if ($err) {
                    Log::error('Erreur cURL lors de l\'envoi du SMS via Brevo', ['error' => $err]);
                    return false;
                }
                
                $responseData = json_decode($response, true);
                
                if (isset($responseData['messageId'])) {
                    Log::info('SMS de confirmation envoyé via Brevo', [
                        'phone' => $phoneNumber, 
                        'reservation_id' => $reservation->id,
                        'message_id' => $responseData['messageId']
                    ]);
                    return true;
                } else {
                    Log::error('Erreur lors de l\'envoi du SMS via Brevo', [
                        'response' => $response
                    ]);
                    return false;
                }
            }
            
            // Si le fournisseur n'est pas Brevo
            Log::warning('Fournisseur SMS ' . $smsConfig['provider'] . ' non pris en charge');
            return false;
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi du SMS', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);
            return false;
        }
    }
}