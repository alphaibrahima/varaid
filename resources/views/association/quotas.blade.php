@extends('layouts.association')

@section('header', 'Gestion des quotas')

@section('content')
    <div class="space-y-6">
        <!-- Cartes des quotas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Quota total -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Quota total</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $quota ? $quota->quantite : 'N/A' }}</p>
                    </div>
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-purple-100 text-purple-500">
                        <i class="fas fa-chart-pie text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Quota restant -->
            @php
                $totalReserved = $grandReserved + $moyenReserved + $petitReserved;
                $remaining = $quota ? ($quota->quantite - $totalReserved) : 0;
                $remainingPercentage = $quota && $quota->quantite > 0 ? round(($remaining / $quota->quantite) * 100) : 0;
                $statusColor = $remainingPercentage > 50 ? 'green' : ($remainingPercentage > 20 ? 'yellow' : 'red');
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Quota restant</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $remaining }}</p>
                    </div>
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-{{ $statusColor }}-100 text-{{ $statusColor }}-500">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-{{ $statusColor }}-500 h-2.5 rounded-full" style="width: {{ $remainingPercentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $remainingPercentage }}% restant</p>
                </div>
            </div>

            <!-- Réservations par taille -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Grands</p>
                    <div class="rounded-full h-8 w-8 flex items-center justify-center bg-blue-100 text-blue-500">
                        <i class="fas fa-sheep text-sm"></i>
                    </div>
                </div>
                <div class="flex items-end">
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $grandReserved }}</p>
                    <p class="text-gray-500 dark:text-gray-400 ml-2">/ {{ $quota ? $quota->grand : 0 }}</p>
                </div>
                <div class="mt-2">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $quota && $quota->grand > 0 ? min(100, round(($grandReserved / $quota->grand) * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Réservations par taille (moyens) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Moyens</p>
                    <div class="rounded-full h-8 w-8 flex items-center justify-center bg-green-100 text-green-500">
                        <i class="fas fa-sheep text-sm"></i>
                    </div>
                </div>
                <div class="flex items-end">
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $moyenReserved }}</p>
                    <p class="text-gray-500 dark:text-gray-400 ml-2">/ {{ $quota ? $quota->moyen : 0 }}</p>
                </div>
                <div class="mt-2">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $quota && $quota->moyen > 0 ? min(100, round(($moyenReserved / $quota->moyen) * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques des quotas -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Graphique de répartition des quotas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Répartition des quotas</h3>
                <div class="h-64">
                    <canvas id="quotaDistributionChart"></canvas>
                </div>
            </div>

            <!-- Graphique de consommation des quotas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Consommation des quotas</h3>
                <div class="h-64">
                    <canvas id="quotaConsumptionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tableau de détail -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Détail des quotas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quota alloué</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Réservations</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Restant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progression</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Ligne grand -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-500">
                                        <i class="fas fa-sheep"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Grands</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->grand : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $grandReserved }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->grand - $grandReserved : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $quota && $quota->grand > 0 ? min(100, round(($grandReserved / $quota->grand) * 100)) : 0 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $quota && $quota->grand > 0 ? round(($grandReserved / $quota->grand) * 100) : 0 }}%</span>
                            </td>
                        </tr>
                        
                        <!-- Ligne moyen -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center text-green-500">
                                        <i class="fas fa-sheep"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Moyens</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->moyen : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $moyenReserved }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->moyen - $moyenReserved : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $quota && $quota->moyen > 0 ? min(100, round(($moyenReserved / $quota->moyen) * 100)) : 0 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $quota && $quota->moyen > 0 ? round(($moyenReserved / $quota->moyen) * 100) : 0 }}%</span>
                            </td>
                        </tr>
                        
                        <!-- Ligne petit -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-500">
                                        <i class="fas fa-sheep"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Petits</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->petit : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $petitReserved }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $quota ? $quota->petit - $petitReserved : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-yellow-600 h-2.5 rounded-full" style="width: {{ $quota && $quota->petit > 0 ? min(100, round(($petitReserved / $quota->petit) * 100)) : 0 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $quota && $quota->petit > 0 ? round(($petitReserved / $quota->petit) * 100) : 0 }}%</span>
                            </td>
                        </tr>
                        
                        <!-- Ligne total -->
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-500">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Total</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $quota ? $quota->quantite : 0 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $totalReserved }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $remaining }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ $quota && $quota->quantite > 0 ? min(100, round(($totalReserved / $quota->quantite) * 100)) : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $quota && $quota->quantite > 0 ? round(($totalReserved / $quota->quantite) * 100) : 0 }}%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique de répartition des quotas
        const quotaDistributionCtx = document.getElementById('quotaDistributionChart').getContext('2d');
        const quotaDistributionChart = new Chart(quotaDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Grand', 'Moyen', 'Petit'],
                datasets: [{
                    data: [
                        {{ $quota ? $quota->grand : 0 }},
                        {{ $quota ? $quota->moyen : 0 }},
                        {{ $quota ? $quota->petit : 0 }}
                    ],
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                    borderWidth: 1
                }]
            },
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

        // Graphique de consommation des quotas
        const quotaConsumptionCtx = document.getElementById('quotaConsumptionChart').getContext('2d');
        const quotaConsumptionChart = new Chart(quotaConsumptionCtx, {
            type: 'bar',
            data: {
                labels: ['Grand', 'Moyen', 'Petit'],
                datasets: [
                    {
                        label: 'Quota alloué',
                        data: [
                            {{ $quota ? $quota->grand : 0 }},
                            {{ $quota ? $quota->moyen : 0 }},
                            {{ $quota ? $quota->petit : 0 }}
                        ],
                        backgroundColor: 'rgba(99, 102, 241, 0.2)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 1
                    },
                    {
                        label: 'Réservations',
                        data: [
                            {{ $grandReserved }},
                            {{ $moyenReserved }},
                            {{ $petitReserved }}
                        ],
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }
                ]
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
                }
            }
        });
    });
</script>
@endsection