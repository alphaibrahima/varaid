<!-- Ajouter ceci sous la section "Navigation Links" dans resources/views/layouts/navigation.blade.php -->

<!-- Association Dashboard Links (Visible only for 'association' role) -->
@if(Auth::check() && Auth::user()->role === 'association')
    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
        <x-nav-link :href="route('association.dashboard')" :active="request()->routeIs('association.dashboard')">
            <i class="fas fa-tachometer-alt mr-1"></i> {{ __('Tableau de bord') }}
        </x-nav-link>
        
        <x-nav-link :href="route('association.buyers')" :active="request()->routeIs('association.buyers*')">
            <i class="fas fa-users mr-1"></i> {{ __('Acheteurs') }}
        </x-nav-link>
        
        <x-nav-link :href="route('association.reservations')" :active="request()->routeIs('association.reservations')">
            <i class="fas fa-calendar-check mr-1"></i> {{ __('Réservations') }}
        </x-nav-link>
        
        <x-nav-link :href="route('association.quotas')" :active="request()->routeIs('association.quotas')">
            <i class="fas fa-chart-pie mr-1"></i> {{ __('Quotas') }}
        </x-nav-link>
    </div>
@endif


    <!-- Ajouter cet indicateur pour les acheteurs -->
    @if(Auth::user()->role === 'buyer')
        @if(Auth::user()->hasVerifiedAffiliation())
            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                <svg class="mr-1 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Affiliation vérifiée
            </span>
        @else
            <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                <svg class="mr-1 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Affiliation non vérifiée
            </span>
        @endif
    @endif

<!-- Ajouter également à la section "Responsive Navigation Menu" -->

<!-- Responsive Association Dashboard Links (visible only for 'association' role) -->
@if(Auth::check() && Auth::user()->role === 'association')
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link :href="route('association.dashboard')" :active="request()->routeIs('association.dashboard')">
            <i class="fas fa-tachometer-alt mr-1"></i> {{ __('Tableau de bord') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('association.buyers')" :active="request()->routeIs('association.buyers*')">
            <i class="fas fa-users mr-1"></i> {{ __('Acheteurs') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('association.reservations')" :active="request()->routeIs('association.reservations')">
            <i class="fas fa-calendar-check mr-1"></i> {{ __('Réservations') }}
        </x-responsive-nav-link>
        
        <x-responsive-nav-link :href="route('association.quotas')" :active="request()->routeIs('association.quotas')">
            <i class="fas fa-chart-pie mr-1"></i> {{ __('Quotas') }}
        </x-responsive-nav-link>
    </div>
@endif