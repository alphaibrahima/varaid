@extends('layouts.association')

@section('header', 'Détails de l\'acheteur')

@section('content')
    <div class="space-y-6">
        <!-- Informations de l'acheteur -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-16 w-16 flex-shrink-0">
                            <img class="h-16 w-16 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($buyer->name) }}&color=7F9CF5&background=EBF4FF&size=128" alt="{{ $buyer->name }}">
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $buyer->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Membre depuis {{ $buyer->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $buyer->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Informations de contact</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-gray-400 w-6"></i>
                                <span class="ml-2 text-gray-600 dark:text-gray-300">{{ $buyer->email }}</span>
                            </div>
                            
                            <div class="flex items-center">
                                <i class="fas fa-phone text-gray-400 w-6"></i>
                                <span class="ml-2 text-gray-600 dark:text-gray-300">{{ $buyer->phone }}</span>
                            </div>
                            
                            @if($buyer->address)
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-gray-400 w-6 mt-1"></i>
                                <span class="ml-2 text-gray-600 dark:text-gray-300">{{ $buyer->address }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Statistiques</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Total des réservations</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ count($reservations) }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Total des agneaux réservés</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ $reservations->sum('quantity') }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Dernière activité</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ $reservations->count() > 0 ? $reservations->sortByDesc('created_at')->first()->created_at->format('d/m/Y') : 'Jamais' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center">
                    <a href="{{ route('association.buyers') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
                    </a>
                    
                    <form method="POST" action="{{ route('association.buyers.toggle-status', $buyer->id) }}">
                        @csrf
                        <button type="submit" class="{{ $buyer->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} px-4 py-2 rounded-md font-medium transition-colors">
                            {{ $buyer->is_active ? 'Désactiver le compte' : 'Activer le compte' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historique des réservations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Historique des réservations</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Créneau</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taille</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($reservations as $reservation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reservation->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reservation->date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reservation->slot->start_time }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($reservation->size) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reservation->quantity }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Aucune réservation trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Script pour rechercher les acheteurs si nécessaire
</script>
@endsection