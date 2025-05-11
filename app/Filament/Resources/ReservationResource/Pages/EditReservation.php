<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            
            // Ajouter un bouton pour voir le reçu
            Actions\Action::make('view_receipt')
                ->label('Voir le reçu')
                ->icon('heroicon-o-document-text')
                ->url(fn () => route('reservation.receipt', ['code' => $this->record->code]))
                ->openUrlInNewTab(),
                
            // Ajouter un bouton pour télécharger le reçu
            Actions\Action::make('download_receipt')
                ->label('Télécharger le reçu')
                ->icon('heroicon-o-document-download')
                ->url(fn () => route('reservation.receipt.download', ['code' => $this->record->code]))
                ->openUrlInNewTab(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}