<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user() && $request->user()->role === $role) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Accès non autorisé à cette section.');
    }
}