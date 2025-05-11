@extends('layouts.association')

@section('header', 'Tableau de bord')

@section('content')
    <div class="space-y-6">

            <!-- Ajouter après le titre du tableau de bord, avant les cartes d'informations -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r shadow-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-blue-700 font-medium">Réservations illimitées disponibles</p>
                    <p class="text-blue-600 mt-1">En tant qu'association, vous pouvez réserver un nombre illimité d'agneaux pour l'Aïd. <a href="{{ route('reservation.index') }}" class="text-blue-800 font-medium underline hover:text-blue-900">Faire une réservation maintenant</a>.</p>
                </div>
            </div>
        </div>
        <!-- Cartes d'informations -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total acheteurs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex items-center">
                <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-100 text-blue-500 mr-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total acheteurs</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $totalBuyers }}</p>
                </div>
            </div>

            <!-- Acheteurs actifs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex items-center">
                <div class="rounded-full h-12 w-12 flex items-center justify-center bg-green-100 text-green-500 mr-4">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Acheteurs actifs</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $activeUsers }}</p>
                </div>
            </div>

            <!-- Quota total -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex items-center">
                <div class="rounded-full h-12 w-12 flex items-center justify-center bg-purple-100 text-purple-500 mr-4">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Quota total</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $quotaData ? $quotaData['total'] : 'N/A' }}</p>
                </div>
            </div>

            <!-- Quota restant -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex items-center">
                <div class="rounded-full h-12 w-12 flex items-center justify-center bg-yellow-100 text-yellow-500 mr-4">
                    <i class="fas fa-box-open text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Quota restant</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $quotaData ? $quotaData['restant'] : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Ajout à faire dans le fichier resources/views/association/dashboard.blade.php -->

        <!-- Localiser la section des "Actions rapides" (si elle existe) ou créer une section dédiée -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Actions rapides
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Bouton pour nouvelle réservation -->
                    <a href="{{ route('reservation.index') }}" class="block p-6 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-150">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-full p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-blue-800">Nouvelle réservation</h4>
                                <p class="text-sm text-blue-600">Réserver des agneaux pour l'Aïd</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Bouton pour les réservations existantes -->
                    <a href="{{ route('association.reservations') }}" class="block p-6 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors duration-150">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-full p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-green-800">Gérer les réservations</h4>
                                <p class="text-sm text-green-600">Voir toutes les réservations</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Bouton pour les quotas -->
                    <a href="{{ route('association.quotas') }}" class="block p-6 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors duration-150">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-full p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-purple-800">Quotas</h4>
                                <p class="text-sm text-purple-600">Gérer les quotas d'agneaux</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Graphique des réservations par jour -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Réservations par jour</h3>
                <div class="h-64">
                    <canvas id="reservationsChart"></canvas>
                </div>
            </div>

            <!-- Graphique de la répartition des tailles -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Répartition des tailles</h3>
                <div class="h-64">
                    <canvas id="sizeDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Dernières réservations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Dernières réservations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acheteur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Créneau</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taille</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($latestReservations as $reservation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reservation->user->name }}</div>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" colspan="6">
                                    Aucune réservation trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('association.reservations') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">Voir toutes les réservations</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Graphique des réservations par jour
    const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
    const reservationsChart = new Chart(reservationsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($reservationsByDay->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m');
            })) !!},
            datasets: [{
                label: 'Réservations',
                data: {{ json_encode($reservationsByDay->pluck('total')) }},
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Graphique de la répartition des tailles
    const sizeCtx = document.getElementById('sizeDistributionChart').getContext('2d');
    const sizeData = {
        labels: ['Grand', 'Moyen', 'Petit'],
        datasets: [{
            data: [
                {{ $sizeDistribution->where('size', 'grand')->first()?->count ?? 0 }},
                {{ $sizeDistribution->where('size', 'moyen')->first()?->count ?? 0 }},
                {{ $sizeDistribution->where('size', 'petit')->first()?->count ?? 0 }}
            ],
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B']
        }]
    };
    
    const sizeChart = new Chart(sizeCtx, {
        type: 'doughnut',
        data: sizeData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection