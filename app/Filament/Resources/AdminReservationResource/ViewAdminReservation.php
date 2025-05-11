<?php

namespace App\Filament\Resources\AdminReservationResource\Pages;

use App\Filament\Resources\AdminReservationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewAdminReservation extends ViewRecord
{
    protected static string $resource = AdminReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('downloadReceipt')
                ->label('Télécharger le reçu')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('reservation.receipt.download', $record->code))
                ->openUrlInNewTab(),
        ];
    }
}