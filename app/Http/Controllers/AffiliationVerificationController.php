<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AffiliationCodeNotification;

class AffiliationVerificationController extends Controller
{
    /**
     * Affiche la vue de vérification d'affiliation
     */
    public function show(Request $request)
    {
        return view('auth.verify-affiliation');
    }

    /**
     * Vérifie le code d'affiliation
     */
    public function verify(Request $request)
    {
        $request->validate([
            'affiliation_code' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        if ($user->affiliation_code === strtoupper($request->affiliation_code)) {
            $user->markAffiliationAsVerified();
            
            // Journaliser la vérification
            \Log::info('Affiliation vérifiée via formulaire', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $user->affiliation_code
            ]);
            
            return redirect()->intended(session('intended_url', route('dashboard')))
                ->with('success', 'Votre affiliation a été vérifiée avec succès!');
        }
        
        // Journaliser la tentative échouée
        \Log::warning('Échec de vérification d\'affiliation via formulaire', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempt' => $request->affiliation_code,
            'expected' => $user->affiliation_code
        ]);
        
        return back()->withErrors([
            'affiliation_code' => 'Le code d\'affiliation est incorrect.'
        ]);
    }

    /**
     * Envoie à nouveau le code d'affiliation
     */
    public function resend(Request $request)
    {
        $user = Auth::user();
        
        // Régénérer le code si nécessaire
        if (!$user->affiliation_code) {
            $user->generateAffiliationCode();
        }
        
        // Envoyer la notification
        $user->notify(new AffiliationCodeNotification());
        
        // Journaliser l'envoi
        \Log::info('Code d\'affiliation renvoyé via formulaire', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        return back()->with('status', 'Un nouveau code d\'affiliation vous a été envoyé.');
    }
}