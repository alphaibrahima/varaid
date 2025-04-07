<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;

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

        return view('reservation.index', compact('slotCounts', 'userAssociation', 'user'));
    }

    public function getSlots($date)
    {
        // Vérifier si la date est valide
        if (!strtotime($date)) {
            return response()->json(['error' => 'Date invalide'], 400);
        }

        // Récupérer les créneaux disponibles pour cette date
        $slots = Slot::where('date', $date)
            ->where('available', true)
            ->orderBy('start_time')
            ->get();

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
            \Log::info('Reservation request received:', $request->all());
    
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }
    
            // Validate the request
            $validated = $request->validate([
                'reservationNumber' => 'required|string',
                'cardholderName' => 'required|string',
                'cardholderEmail' => 'required|email',
                'slotId' => 'required|integer|exists:slots,id',
                'quantity' => 'required|integer|min:1|max:5',
                'paymentIntentId' => 'required|string',
                'owners' => 'required|array|min:1',
                'owners.*.firstname' => 'required|string',
                'owners.*.lastname' => 'required|string'
            ]);
    
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'slot_id' => $validated['slotId'],
                'association_id' => $user->association_id ?? null,
                'size' => 'grand',
                'quantity' => $validated['quantity'],
                'code' => $validated['reservationNumber'],
                'status' => 'confirmed',
                'date' => now(),
                'owners_data' => json_encode($validated['owners']),
                'payment_intent_id' => $validated['paymentIntentId']
            ]);
    
            // Charger les relations nécessaires pour la notification
            $reservation->load(['user', 'slot', 'association']);
    
            // Envoi de l'email de confirmation avec le reçu en PDF
            try {
                // Envoi de l'email de confirmation avec le reçu en PDF
                $user->notify(new \App\Notifications\ReservationConfirmation($reservation));
            
                // Envoyer également un email à l'adresse fournie dans le formulaire si différente
                if ($validated['cardholderEmail'] !== $user->email) {
                    \Notification::route('mail', [
                        $validated['cardholderEmail'] => $validated['cardholderName']
                    ])->notify(new \App\Notifications\ReservationConfirmation($reservation));
                }
            } catch (\Exception $emailError) {
                \Log::warning('Erreur lors de l\'envoi d\'email: ' . $emailError->getMessage());
                // Ne pas bloquer la confirmation de réservation si l'email échoue
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Réservation créée avec succès',
                'data' => $reservation,
                'redirectUrl' => route('reservation.receipt', ['code' => $reservation->code])
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Reservation error:', [
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
}
