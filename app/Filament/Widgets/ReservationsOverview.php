<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ReservationsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        // Filtre pour les associations
        $user = auth()->user();
        $query = Reservation::query();
        
        if ($user && $user->role === 'association') {
            $query->where('association_id', $user->id);
        }
        
        // Statistiques globales
        $totalReservations = $query->count();
        $confirmedReservations = $query->where('status', 'confirmed')->count();
        $canceledReservations = $query->where('status', 'canceled')->count();
        
        return [
            Stat::make('Total des réservations', $totalReservations)
                ->description('Toutes réservations confondues')
                ->color('primary'),
                
            Stat::make('Réservations confirmées', $confirmedReservations)
                ->description($totalReservations > 0 ? round(($confirmedReservations / $totalReservations) * 100) . '%' : '0%')
                ->color('success'),
                
            Stat::make('Réservations annulées', $canceledReservations)
                ->description($totalReservations > 0 ? round(($canceledReservations / $totalReservations) * 100) . '%' : '0%')
                ->color('danger'),
        ];
    }
}