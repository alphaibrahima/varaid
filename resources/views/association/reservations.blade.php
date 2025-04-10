@extends('layouts.association')

@section('header', 'Réservations')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Liste des réservations</h3>
        
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" id="search" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <select id="filter-status" class="py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous statuts</option>
                    <option value="confirmed">Confirmé</option>
                    <option value="pending">En attente</option>
                    <option value="canceled">Annulé</option>
                </select>
                
                <select id="filter-size" class="py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Taille</option>
                    <option value="grand">Grand</option>
                    <option value="moyen">Moyen</option>
                    <option value="petit">Petit</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Vue desktop et tablette -->
    <div class="hidden sm:block">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acheteur</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Créneau</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taille</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qté</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="reservations-table">
                @forelse($reservations as $reservation)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ $reservation->code }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs">
                                    {{ strtoupper(substr($reservation->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="ml-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">{{ $reservation->user->name ?? 'Inconnu' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ substr($reservation->user->email ?? '', 0, 15) }}{{ strlen($reservation->user->email ?? '') > 15 ? '...' : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ $reservation->date->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ substr($reservation->slot->start_time ?? '', 0, 5) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ ucfirst($reservation->size) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                            {{ $reservation->quantity }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                   ($reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($reservation->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                            Aucune réservation trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Vue mobile (cartes) -->
    <div class="sm:hidden">
        <ul class="divide-y divide-gray-200 dark:divide-gray-700" id="reservations-table-mobile">
            @forelse($reservations as $reservation)
                <li class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm">
                                    {{ strtoupper(substr($reservation->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reservation->user->name ?? 'Inconnu' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Code: {{ $reservation->code }}</div>
                                </div>
                            </div>
                            <div class="mt-2 ml-11">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <div><strong>Date:</strong> {{ $reservation->date->format('d/m/Y') }}</div>
                                    <div><strong>Créneau:</strong> {{ substr($reservation->slot->start_time ?? '', 0, 5) }}</div>
                                    <div><strong>Taille:</strong> {{ ucfirst($reservation->size) }}</div>
                                    <div><strong>Quantité:</strong> {{ $reservation->quantity }}</div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                               ($reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($reservation->status) }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Aucune réservation trouvée.
                </li>
            @endforelse
        </ul>
    </div>
    
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $reservations->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Récupération des éléments
        const searchInput = document.getElementById('search');
        const filterStatus = document.getElementById('filter-status');
        const filterSize = document.getElementById('filter-size');
        
        // Fonction de filtrage améliorée
        function applyFilters() {
            const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
            const statusValue = filterStatus ? filterStatus.value.toLowerCase() : '';
            const sizeValue = filterSize ? filterSize.value.toLowerCase() : '';
            
            // Desktop/tablet view
            const table = document.getElementById('reservations-table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    // Récupérer spécifiquement les informations de l'acheteur (2ème cellule)
                    const buyerCell = row.querySelector('td:nth-child(2)');
                    const buyerInfo = buyerCell ? buyerCell.textContent.toLowerCase() : '';
                    
                    // Obtenir le contenu complet pour d'autres recherches
                    const fullContent = row.textContent.toLowerCase();
                    
                    // Vérifier le statut (7e cellule)
                    const statusCell = row.querySelector('td:nth-child(7)');
                    const statusText = statusCell ? statusCell.textContent.toLowerCase() : '';
                    
                    // Vérifier la taille (5e cellule)
                    const sizeCell = row.querySelector('td:nth-child(5)');
                    const sizeText = sizeCell ? sizeCell.textContent.toLowerCase() : '';
                    
                    // Appliquer les filtres
                    // Pour la recherche, prioriser les informations de l'acheteur
                    const matchesSearch = searchValue === '' || 
                                          buyerInfo.includes(searchValue) || 
                                          fullContent.includes(searchValue);
                    
                    const matchesStatus = statusValue === '' || statusText.includes(statusValue);
                    const matchesSize = sizeValue === '' || sizeText.includes(sizeValue);
                    
                    // Afficher ou masquer la ligne
                    row.style.display = (matchesSearch && matchesStatus && matchesSize) ? '' : 'none';
                });
            }
            
            // Mobile view
            const mobileList = document.getElementById('reservations-table-mobile');
            if (mobileList) {
                const items = mobileList.querySelectorAll('li');
                
                items.forEach(item => {
                    // Récupérer les informations de l'acheteur
                    const buyerName = item.querySelector('.font-medium');
                    const buyerEmail = buyerName ? buyerName.nextElementSibling : null;
                    
                    const buyerInfo = [
                        buyerName ? buyerName.textContent.toLowerCase() : '',
                        buyerEmail ? buyerEmail.textContent.toLowerCase() : ''
                    ].join(' ');
                    
                    // Obtenir le contenu complet pour d'autres recherches
                    const fullContent = item.textContent.toLowerCase();
                    
                    // Vérifier le statut
                    const statusElement = item.querySelector('span[class*="rounded-full"]');
                    const statusText = statusElement ? statusElement.textContent.toLowerCase() : '';
                    
                    // Vérifier la taille
                    const sizeInfo = item.textContent.match(/Taille:\s*(\w+)/i);
                    const sizeText = sizeInfo ? sizeInfo[1].toLowerCase() : '';
                    
                    // Appliquer les filtres
                    const matchesSearch = searchValue === '' || 
                                          buyerInfo.includes(searchValue) || 
                                          fullContent.includes(searchValue);
                    
                    const matchesStatus = statusValue === '' || statusText.includes(statusValue);
                    const matchesSize = sizeValue === '' || sizeText.includes(sizeValue);
                    
                    // Afficher ou masquer l'élément
                    item.style.display = (matchesSearch && matchesStatus && matchesSize) ? '' : 'none';
                });
            }
        }
        
        // Attacher les événements
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
        
        if (filterStatus) {
            filterStatus.addEventListener('change', applyFilters);
        }
        
        if (filterSize) {
            filterSize.addEventListener('change', applyFilters);
        }
        
        // Appliquer les filtres une fois lors du chargement
        applyFilters();
    });
</script>
@endsection