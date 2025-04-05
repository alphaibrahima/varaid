<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Associations', User::where('role', 'association')->count())
                ->description('Total des associations')
                ->color('success'),

            Stat::make('Acheteurs', User::where('role', 'buyer')->count())
                ->description('Total des acheteurs')
                ->color('warning'),

            Stat::make('Réservations', Reservation::whereMonth('created_at', now()->month)->count())
                ->description('Ce mois')
                ->color('primary'),
        ];
    }
}
