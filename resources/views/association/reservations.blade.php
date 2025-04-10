@extends('layouts.association')

@section('header', 'Réservations')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:justify-between md:items-center space-y-3 md:space-y-0">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Liste des réservations</h3>
            
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                <div class="relative">
                    <input type="text" id="search" placeholder="Rechercher..." class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <select id="filter-status" class="py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="confirmed">Confirmé</option>
                        <option value="pending">En attente</option>
                        <option value="canceled">Annulé</option>
                    </select>
                    
                    <select id="filter-size" class="py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les tailles</option>
                        <option value="grand">Grand</option>
                        <option value="moyen">Moyen</option>
                        <option value="petit">Petit</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acheteur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date réservation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jour & Créneau</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taille</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="reservations-table">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reservation->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 flex-shrink-0">
                                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($reservation->user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $reservation->user->name }}">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reservation->user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reservation->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ $reservation->date->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->slot->start_time }}</div>
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
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                Aucune réservation trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $reservations->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestionnaire de filtre de statut
        document.getElementById('filter-status').addEventListener('change', applyFilters);
        
        // Gestionnaire de filtre de taille
        document.getElementById('filter-size').addEventListener('change', applyFilters);
        
        // Gestionnaire de recherche
        document.getElementById('search').addEventListener('input', applyFilters);
        
        function applyFilters() {
            const status = document.getElementById('filter-status').value.toLowerCase();
            const size = document.getElementById('filter-size').value.toLowerCase();
            const search = document.getElementById('search').value.toLowerCase();
            
            const rows = document.querySelectorAll('#reservations-table tr');
            
            rows.forEach(row => {
                let statusMatch = true;
                let sizeMatch = true;
                let searchMatch = true;
                
                // Vérifier le statut
                if (status) {
                    const statusCell = row.querySelector('td:nth-child(7) span');
                    if (statusCell) {
                        statusMatch = statusCell.textContent.trim().toLowerCase().includes(status);
                    }
                }
                
                // Vérifier la taille
                if (size) {
                    const sizeCell = row.querySelector('td:nth-child(5)');
                    if (sizeCell) {
                        sizeMatch = sizeCell.textContent.trim().toLowerCase().includes(size);
                    }
                }
                
                // Recherche globale
                if (search) {
                    searchMatch = Array.from(row.querySelectorAll('td')).some(cell => 
                        cell.textContent.toLowerCase().includes(search)
                    );
                }
                
                // Afficher/masquer la ligne en fonction des filtres
                if (statusMatch && sizeMatch && searchMatch) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
    });
</script>
@endsection