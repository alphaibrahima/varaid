<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quota;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssociationDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'association
    */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'association') {
                return redirect('/dashboard')->with('error', 'Accès non autorisé à cette section.');
            }
            return $next($request);
        });
    }

    
    public function index()
    {
        $association = Auth::user();
        
        // Récupérer les statistiques
        $totalBuyers = User::where('association_id', $association->id)->where('role', 'buyer')->count();
        $activeUsers = User::where('association_id', $association->id)->where('role', 'buyer')->where('is_active', true)->count();
        
        // Récupérer les quotas
        $quota = Quota::where('association_id', $association->id)->first();
        $quotaData = $quota ? [
            'total' => $quota->quantite,
            'grand' => $quota->grand ?? 0,
            'moyen' => $quota->moyen ?? 0,
            'petit' => $quota->petit ?? 0,
            'restant' => $quota->quantite - Reservation::where('association_id', $association->id)->sum('quantity')
        ] : null;
        
        // Récupérer les dernières réservations
        $latestReservations = Reservation::with(['user', 'slot'])
            ->where('association_id', $association->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Récupérer les données pour le graphique des réservations par jour
        $reservationsByDay = Reservation::where('association_id', $association->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse();
        
        // Récupérer les données pour le graphique des tailles
        $sizeDistribution = Reservation::where('association_id', $association->id)
            ->select('size', DB::raw('count(*) as count'))
            ->groupBy('size')
            ->get();
        
        return view('association.dashboard', compact(
            'totalBuyers', 
            'activeUsers', 
            'quotaData', 
            'latestReservations', 
            'reservationsByDay',
            'sizeDistribution'
        ));
    }

    /**
     * Affiche la liste des acheteurs affiliés à l'association
     */
    public function buyers()
    {
        $association = Auth::user();
        
        $buyers = User::where('association_id', $association->id)
            ->where('role', 'buyer')
            ->orderBy('name')
            ->paginate(10);
        
        return view('association.buyers', compact('buyers'));
    }

    /**
     * Affiche les détails d'un acheteur spécifique
     */
    public function buyerDetails($id)
    {
        $association = Auth::user();
        
        $buyer = User::where('id', $id)
            ->where('association_id', $association->id)
            ->where('role', 'buyer')
            ->firstOrFail();
        
        $reservations = Reservation::with(['slot'])
            ->where('user_id', $buyer->id)
            ->where('association_id', $association->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('association.buyer-details', compact('buyer', 'reservations'));
    }

    /**
     * Affiche toutes les réservations de l'association
     */
    public function reservations()
    {
        $association = Auth::user();
        
        $reservations = Reservation::with(['user', 'slot'])
            ->where('association_id', $association->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('association.reservations', compact('reservations'));
    }

    /**
     * Active ou désactive un compte d'acheteur
     */
    public function toggleBuyerStatus(Request $request, $id)
    {
        $association = Auth::user();
        
        $buyer = User::where('id', $id)
            ->where('association_id', $association->id)
            ->where('role', 'buyer')
            ->firstOrFail();
        
        $buyer->is_active = !$buyer->is_active;
        $buyer->save();
        
        return redirect()->back()->with('success', 'Le statut du compte a été mis à jour avec succès.');
    }

    /**
     * Affiche les quotas de l'association
     */
    public function quotas()
    {
        $association = Auth::user();
        
        $quota = Quota::where('association_id', $association->id)->first();
        
        // Calculer les réservations par taille
        $reservationsBySize = Reservation::where('association_id', $association->id)
            ->select('size', DB::raw('SUM(quantity) as total'))
            ->groupBy('size')
            ->get()
            ->pluck('total', 'size')
            ->toArray();
        
        $grandReserved = $reservationsBySize['grand'] ?? 0;
        $moyenReserved = $reservationsBySize['moyen'] ?? 0;
        $petitReserved = $reservationsBySize['petit'] ?? 0;
        
        return view('association.quotas', compact('quota', 'grandReserved', 'moyenReserved', 'petitReserved'));
    }
}