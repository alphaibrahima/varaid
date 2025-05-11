<?php

namespace App\Filament\Resources\AdminReservationResource\Pages;

use App\Filament\Resources\AdminReservationResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditAdminReservation extends EditRecord
{
    protected static string $resource = AdminReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}