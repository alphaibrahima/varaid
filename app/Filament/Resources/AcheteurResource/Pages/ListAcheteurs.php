<?php

namespace App\Filament\Resources\AcheteurResource\Pages;

use App\Filament\Resources\AcheteurResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcheteurs extends ListRecords
{
    protected static string $resource = AcheteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make() // AJOUTER CETTE LIGNE
                ->label('Nouvel Acheteur'),
        ];
    }
}