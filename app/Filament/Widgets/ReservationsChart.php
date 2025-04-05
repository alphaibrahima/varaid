<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ReservationsChart extends ChartWidget
{
    protected static ?string $heading = 'Réservations';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Réservations',
                    'data' => [65, 59, 80, 81, 56, 55, 40],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}