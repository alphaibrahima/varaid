@extends('layouts.buyer')

@section('header', 'Tableau de bord')

@section('content')
<div class="space-y-6">
    <!-- Carte de bienvenue -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                Bonjour {{ Auth::user()->firstname ?? Auth::user()->name }} 👋
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Bienvenue dans votre espace acheteur. Ici, vous pouvez gérer vos réservations d'agneaux pour l'Aïd.
            </p>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Actions rapides
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('reservation.index') }}" class="block p-6 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-full p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-blue-800">Nouvelle réservation</h4>
                            <p class="text-sm text-blue-600">Réservez un agneau pour l'Aïd</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('dashboard') }}?tab=mes-reservations" class="block p-6 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-full p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-green-800">Mes réservations</h4>
                            <p class="text-sm text-green-600">Gérer mes réservations</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('profile.edit') }}" class="block p-6 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-full p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-purple-800">Mon profil</h4>
                            <p class="text-sm text-purple-600">Modifier mes informations</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('tutorials') }}" class="block p-6 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition-colors duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-500 rounded-full p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-amber-800">Tutoriels</h4>
                            <p class="text-sm text-amber-600">Guides vidéo pour réserver</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Mes réservations -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Mes dernières réservations
            </h3>
        </div>
        <div class="p-0">
            @php
                $reservations = Auth::user()->reservations()->with(['slot'])->latest()->take(5)->get();
            @endphp
            
            @if(count($reservations) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Référence</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Heure</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $reservation->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reservation->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reservation->slot->start_time }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reservation->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                            ($reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('reservation.receipt', $reservation->code) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 text-right">
                    <a href="{{ route('dashboard') }}?tab=mes-reservations" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                        Voir toutes mes réservations →
                    </a>
                </div>
            @else
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucune réservation</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vous n'avez pas encore fait de réservation.</p>
                    <div class="mt-6">
                        <a href="{{ route('reservation.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Faire une réservation
                        </a>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Calendrier des prochaines disponibilités -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Prochaines disponibilités
        </h3>
    </div>
    <div class="p-6">
        @php
            $availableDates = \App\Models\Slot::where('date', '>=', now())
                ->where('available', true)
                ->select('date')
                ->distinct()
                ->orderBy('date')
                ->take(5)
                ->get();
        @endphp
        
        @if(count($availableDates) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($availableDates as $date)
                    @php
                        $slotsCount = \App\Models\Slot::where('date', $date->date)
                            ->where('available', true)
                            ->count();
                    @endphp
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $date->date->format('d/m/Y') }}
                                </div>
                                <div class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $date->date->translatedFormat('l') }}
                                </div>
                            </div>
                            <div class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                {{ $slotsCount }} créneaux
                            </div>
                        </div>
                        <div class="mt-3 text-right">
                            <a href="{{ route('reservation.index') }}?date={{ $date->date->format('Y-m-d') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Réserver →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                Aucune disponibilité prochaine n'est trouvée. Veuillez vérifier ultérieurement.
            </div>
        @endif
    </div>
</div>

<!-- Informations de l'association -->
@if(Auth::user()->association)
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Votre association
            </h3>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                <div>
                    <h4 class="text-xl font-medium text-gray-900 dark:text-white">
                        {{ Auth::user()->association->name }}
                    </h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ Auth::user()->association->address }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ Auth::user()->association->phone }}
                    </p>
                </div>
                
                <div class="mt-4 sm:mt-0 bg-indigo-50 dark:bg-indigo-900 p-3 rounded-lg">
                    <div class="text-sm text-indigo-800 dark:text-indigo-200 font-medium">Informations de contact</div>
                    <div class="text-xs text-indigo-600 dark:text-indigo-300 mt-1">
                        <i class="fas fa-envelope mr-1"></i> {{ Auth::user()->association->email }}
                    </div>
                    <div class="text-xs text-indigo-600 dark:text-indigo-300 mt-1">
                        <i class="fas fa-phone mr-1"></i> {{ Auth::user()->association->phone }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
@endsection