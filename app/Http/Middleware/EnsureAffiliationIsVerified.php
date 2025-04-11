<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAffiliationIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || 
            ($request->user()->role === 'buyer' && !$request->user()->hasVerifiedAffiliation())) {
            // Stocker l'intention de l'utilisateur pour la redirection après vérification
            session()->put('intended_url', $request->url());
            
            return redirect()->route('affiliation.verify');
        }

        return $next($request);
    }
}