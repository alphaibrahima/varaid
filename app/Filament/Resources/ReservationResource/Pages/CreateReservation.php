<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer un code unique s'il n'existe pas
        $data['code'] = $data['code'] ?? 'R-' . Str::random(6);
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}