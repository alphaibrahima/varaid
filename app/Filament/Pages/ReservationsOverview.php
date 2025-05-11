<?php

namespace App\Filament\Pages;

use App\Models\Reservation;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Widgets\ReservationsOverview as ReservationsOverviewWidget;


class ReservationsOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.reservations-overview';
    protected static ?string $navigationLabel = 'Vue d\'ensemble des réservations';
    protected static ?string $title = 'Vue d\'ensemble des réservations';
    protected static ?string $navigationGroup = 'Réservations';
    protected static ?int $navigationSort = 1;
    
    public function getHeading(): string
    {
        return 'Vue d\'ensemble des réservations';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReservationsOverviewWidget::class,
        ];
    }
}