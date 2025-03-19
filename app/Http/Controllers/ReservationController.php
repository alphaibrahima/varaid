<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index()
    {
       
    $slotCounts = Slot::select('date', DB::raw('count(*) as total'))
    ->groupBy('date')
    ->orderBy('date', 'asc')
    ->orderBy('start_time')
    ->get();
    
        return view('reservation.index' , compact('slotCounts'));
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
}
