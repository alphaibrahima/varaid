<?php

namespace App\Filament\Resources\QuotaResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Filament\Resources\QuotaResource;
use App\Models\Quota;
use Illuminate\Validation\ValidationException;

class CreateQuota extends CreateRecord
{
    protected static string $resource = QuotaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Vérifier si un quota existe déjà pour l'association sélectionnée
        if (Quota::where('association_id', $data['association_id'])->exists()) {
            // Afficher la notification d'erreur
            Notification::make()
                ->title('Erreur')
                ->danger()
                ->body('Un quota existe déjà pour cette association.')
                ->send();

            // Lever une exception de validation pour empêcher la création
            throw ValidationException::withMessages([
                'association_id' => 'Un quota existe déjà pour cette association.',
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
