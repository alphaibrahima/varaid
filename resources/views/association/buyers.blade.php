@extends('layouts.association')

@section('header', 'Gestion des acheteurs')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Liste des acheteurs</h3>
        <div class="relative w-full md:w-64">
            <input type="text" id="search" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Table pour écrans moyens et grands -->
    <div class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Téléphone</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Réservations</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="buyers-table-desktop">
                    @forelse($buyers as $buyer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                        {{ strtoupper(substr($buyer->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $buyer->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $buyer->email }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $buyer->phone }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $buyer->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $buyer->reservations_count ?? 0 }} réservation(s)
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('association.buyers.details', $buyer->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Détails</a>
                                <form method="POST" action="{{ route('association.buyers.toggle-status', $buyer->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $buyer->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $buyer->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                Aucun acheteur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Affichage mobile en cartes -->
    <div class="md:hidden">
        <ul class="divide-y divide-gray-200 dark:divide-gray-700" id="buyers-table-mobile">
            @forelse($buyers as $buyer)
                <li class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                {{ strtoupper(substr($buyer->name, 0, 2)) }}
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $buyer->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $buyer->email }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $buyer->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span class="block mt-1"><strong>Téléphone:</strong> {{ $buyer->phone }}</span>
                            <span class="block mt-1"><strong>Réservations:</strong> {{ $buyer->reservations_count ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-between">
                        <a href="{{ route('association.buyers.details', $buyer->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            Détails
                        </a>
                        <form method="POST" action="{{ route('association.buyers.toggle-status', $buyer->id) }}">
                            @csrf
                            <button type="submit" class="{{ $buyer->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }} text-sm">
                                @if($buyer->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Désactiver
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Activer
                                @endif
                            </button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Aucun acheteur trouvé.
                </li>
            @endforelse
        </ul>
    </div>
    
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $buyers->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                
                // Filtrer la table desktop
                const desktopRows = document.querySelectorAll('#buyers-table-desktop tr');
                desktopRows.forEach(row => {
                    if (!row.querySelector('td')) return; // Skip header row
                    const textContent = row.textContent.toLowerCase();
                    row.style.display = textContent.includes(searchValue) ? '' : 'none';
                });
                
                // Filtrer les cartes mobiles
                const mobileItems = document.querySelectorAll('#buyers-table-mobile li');
                mobileItems.forEach(item => {
                    const textContent = item.textContent.toLowerCase();
                    item.style.display = textContent.includes(searchValue) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection