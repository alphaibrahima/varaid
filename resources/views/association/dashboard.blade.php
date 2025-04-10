@extends('layouts.association')

@section('header', 'Tableau de bord')

@section('content')
    <div class="space-y-6">
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