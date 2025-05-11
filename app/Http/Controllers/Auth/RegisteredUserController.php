<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use App\Events\AcheteurRegistered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): View
    {
        // Récupérer les associations actives
        $associations = User::where('role', 'association')
                            ->when(Schema::hasColumn('users', 'is_active'), function ($query) {
                                $query->where('is_active', true);
                            })
                            ->get();

        // Retourner la vue avec les associations
        return view('auth.register', [
            'associations' => $associations, // Passer les associations à la vue
        ]);
    }

    /**
     * Traite une demande d'inscription.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firstname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'unique:users'],
            'full_address' => ['required', 'string'],
            'association_id' => ['required', 'exists:users,id'],
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'full_address' => $request->full_address,
            'association_id' => $request->association_id,
            'role' => 'buyer',
        ]);

        // Générer le code d'affiliation
        $affiliationCode = $user->generateAffiliationCode();
    
        event(new Registered($user));
        event(new AcheteurRegistered($user));
    
        Auth::login($user);
    
        return redirect(RouteServiceProvider::HOME);
    }
}