<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord approprié selon le rôle de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        
        switch ($user->role) {
            case 'admin':
                // Pour les administrateurs, on pourrait rediriger vers un dashboard admin
                return view('admin.dashboard');
                
            case 'association':
                // Pour les associations, on utilise le tableau de bord des associations
                return redirect()->route('association.dashboard');
                
            case 'buyer':
                // Pour les acheteurs, on utilise notre nouveau dashboard acheteur
                return view('buyer.dashboard');
                
            default:
                // Par défaut, on utilise le dashboard standard
                return view('dashboard');
        }
    }
}