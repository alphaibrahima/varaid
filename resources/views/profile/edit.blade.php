@php
    // Déterminer le layout à utiliser en fonction du rôle
    $layout = 'layouts.app';
    if (Auth::user()->role === 'association') {
        $layout = 'layouts.association';
    } elseif (Auth::user()->role === 'buyer') {
        $layout = 'layouts.buyer';
    }
    // Pour les autres rôles (admin), vous pourriez ajouter d'autres conditions ici
@endphp

@extends($layout)

@section('header', 'Modifier votre profil')

@section('content')
<div class="space-y-6">
    <!-- Section du profil -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Informations du profil') }}
            </h3>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <!-- Nom -->
                <div>
                    <x-input-label for="name" :value="__('Nom')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Prénom (si présent dans votre base) -->
                @if(isset($user->firstname))
                <div class="mt-4">
                    <x-input-label for="firstname" :value="__('Prénom')" />
                    <x-text-input id="firstname" name="firstname" type="text" class="mt-1 block w-full" :value="old('firstname', $user->firstname)" required />
                    <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
                </div>
                @endif

                <!-- Email -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Téléphone -->
                <div class="mt-4">
                    <x-input-label for="phone" :value="__('Téléphone')" />
                    <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <!-- Adresse -->
                <div class="mt-4">
                    <x-input-label for="full_address" :value="__('Adresse complète')" />
                    <x-textarea id="full_address" name="full_address" class="mt-1 block w-full" rows="3" required>{{ old('full_address', $user->full_address) }}</x-textarea>
                    <x-input-error :messages="$errors->get('full_address')" class="mt-2" />
                </div>

                <!-- Bouton de soumission -->
                <div class="flex items-center justify-end mt-4">
                    <x-primary-button>
                        {{ __('Enregistrer') }}
                    </x-primary-button>

                    @if (session('status') === 'profile-updated')
                        <p class="ml-3 text-sm text-green-600 dark:text-green-400">
                            {{ __('Profil mis à jour.') }}
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Section du mot de passe -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Mettre à jour le mot de passe') }}
            </h3>
        </div>

        <div class="p-6">
            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="current_password" :value="__('Mot de passe actuel')" />
                    <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Nouveau mot de passe')" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button>
                        {{ __('Enregistrer') }}
                    </x-primary-button>

                    @if (session('status') === 'password-updated')
                        <p class="ml-3 text-sm text-green-600 dark:text-green-400">
                            {{ __('Mot de passe mis à jour.') }}
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Section Suppression de compte -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Supprimer le compte') }}
            </h3>
        </div>

        <div class="p-6">
            <x-danger-button
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            >{{ __('Supprimer le compte') }}</x-danger-button>

            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Êtes-vous sûr de vouloir supprimer votre compte ?') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Veuillez saisir votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.') }}
                    </p>

                    <div class="mt-6">
                        <x-input-label for="password" value="{{ __('Mot de passe') }}" class="sr-only" />

                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="{{ __('Mot de passe') }}"
                        />

                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Annuler') }}
                        </x-secondary-button>

                        <x-danger-button class="ms-3">
                            {{ __('Supprimer le compte') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
</div>
@endsection