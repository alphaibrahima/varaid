@extends('layouts.app')

<section class="max-w-xl mx-auto">
    <header class="text-center mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Informations du profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Mettez à jour vos informations personnelles et votre adresse e-mail.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Nom -->
        <div class="mb-4">
            <label for="name" class="block font-medium text-sm text-gray-700">{{ __('Nom') }}</label>
            <input id="name" name="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('name', $user->name) }}" required autofocus>
            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <!-- Prénom -->
        <div class="mb-4">
            <label for="firstname" class="block font-medium text-sm text-gray-700">{{ __('Prénom') }}</label>
            <input id="firstname" name="firstname" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('firstname', $user->firstname) }}" required>
            @error('firstname')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block font-medium text-sm text-gray-700">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('email', $user->email) }}" required>
            @error('email')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <!-- Téléphone -->
        <div class="mb-4">
            <label for="phone" class="block font-medium text-sm text-gray-700">{{ __('Téléphone') }}</label>
            <input id="phone" name="phone" type="tel" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="{{ old('phone', $user->phone) }}" required>
            @error('phone')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <!-- Adresse complète -->
        <div class="mb-4">
            <label for="full_address" class="block font-medium text-sm text-gray-700">{{ __('Adresse complète') }}</label>
            <textarea id="full_address" name="full_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="3" required>{{ old('full_address', $user->full_address) }}</textarea>
            @error('full_address')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="flex items-center justify-center mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Enregistrer') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600 ml-3">
                    {{ __('Enregistré.') }}
                </p>
            @endif
        </div>
    </form>

    <!-- Section Mise à jour du mot de passe -->
    <div class="mt-10 pt-10 border-t border-gray-200">
        <header class="text-center mb-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Mise à jour du mot de passe') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester en sécurité.') }}
            </p>
        </header>

        <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('put')

            <!-- Mot de passe actuel -->
            <div class="mb-4">
                <label for="current_password" class="block font-medium text-sm text-gray-700">{{ __('Mot de passe actuel') }}</label>
                <input id="current_password" name="current_password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" autocomplete="current-password">
                @error('current_password', 'updatePassword')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <!-- Nouveau mot de passe -->
            <div class="mb-4">
                <label for="password" class="block font-medium text-sm text-gray-700">{{ __('Nouveau mot de passe') }}</label>
                <input id="password" name="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" autocomplete="new-password">
                @error('password', 'updatePassword')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <!-- Confirmation du mot de passe -->
            <div class="mb-4">
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">{{ __('Confirmer le mot de passe') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" autocomplete="new-password">
                @error('password_confirmation', 'updatePassword')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="flex items-center justify-center mt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Enregistrer') }}
                </button>

                @if (session('status') === 'password-updated')
                    <p class="text-sm text-green-600 ml-3">
                        {{ __('Enregistré.') }}
                    </p>
                @endif
            </div>
        </form>
    </div>
</section>