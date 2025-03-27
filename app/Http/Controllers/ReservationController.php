<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

            $validated = $request->validate([
                'reservationNumber' => 'required|string',
                'cardholderName' => 'required|string',
                'cardholderEmail' => 'required|email',
                'slotId' => 'required|integer',
                'quantity' => 'required|integer|min:1|max:5'
            ]);

            $slot = Slot::find($validated['slotId']);
            if (!$slot) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid slot selected'
                ], 404);
            }

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'slot_id' => $validated['slotId'],
                'association_id' => $user->association ? $user->association->id : null,
                'size' => 'grand',
                'quantity' => $validated['quantity'],
                'code' => $validated['reservationNumber'],
                'status' => 'pending',
                'date' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Réservation créée avec succès',
                'data' => $reservation
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
}
