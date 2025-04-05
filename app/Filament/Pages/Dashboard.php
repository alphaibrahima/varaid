<?php

namespace App\Filament\Pages;
use App\Filament\Widgets\ReservationsChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\QuotaTable;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            ReservationsChart::class, // Sans le namespace complet
            QuotaTable::class,
        ];
    }
}