<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Avant de pouvoir effectuer une réservation, vous devez vérifier votre affiliation avec votre association.') }}
        {{ __('Un code d\'affiliation vous a été envoyé par email et SMS.') }}
    </div>

    @if (session('status') === 'affiliation-code-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('Un nouveau code d\'affiliation a été envoyé.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('affiliation.verify') }}">
        @csrf

        <div>
            <x-input-label for="affiliation_code" :value="__('Code d\'affiliation')" />
            <x-text-input id="affiliation_code" class="block mt-1 w-full" type="text" name="affiliation_code" :value="old('affiliation_code')" required autofocus />
            <x-input-error :messages="$errors->get('affiliation_code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <form method="POST" action="{{ route('affiliation.resend') }}">
                @csrf
                <x-secondary-button type="submit">
                    {{ __('Renvoyer le code') }}
                </x-secondary-button>
            </form>

            <x-primary-button>
                {{ __('Vérifier') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>