<?php

namespace App\Filament\Resources\AcheteurResource\Pages;

use App\Filament\Resources\AcheteurResource;
use App\Notifications\AcheteurCredentialsNotification;
use App\Notifications\AccountCreatedSmsNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateAcheteur extends CreateRecord
{
    protected static string $resource = AcheteurResource::class;
    
    // Ajouter la propriété password
    protected $password;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer le mot de passe temporaire si nécessaire
        $data['phone'] = $data['country_code'] . preg_replace('/\D/', '', $data['local_phone']);
        unset($data['country_code'], $data['local_phone']);
        
        // Si le mot de passe n'est pas fourni, générer un mot de passe temporaire
        if (empty($data['password'])) {
            $this->password = Str::random(10);
            $data['password'] = bcrypt($this->password);
        } else {
            // Si un mot de passe a été fourni par l'utilisateur, le conserver pour la notification
            $this->password = $data['password'];
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // // Envoyer la notification seulement si nous avons un mot de passe
        // if (isset($this->password)) {
        //     // Envoyer la notification
        //     $this->record->notify(
        //         new AcheteurCredentialsNotification($this->password)
        //     );
        // }

            // Envoyer la notification seulement si nous avons un mot de passe
        if (isset($this->password)) {
            // Envoyer la notification par email
            $this->record->notify(
                new AcheteurCredentialsNotification($this->password)
            );
            
            // Envoyer également la notification par SMS
            $this->record->notify(
                new AccountCreatedSmsNotification()
            );
        }
    }
}