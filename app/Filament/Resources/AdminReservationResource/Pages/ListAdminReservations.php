<?php

namespace App\Filament\Resources\AdminReservationResource\Pages;

use App\Filament\Resources\AdminReservationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListAdminReservations extends ListRecords
{
    protected static string $resource = AdminReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Créer des réservations')
                ->icon('heroicon-o-plus'),
        ];
    }
}