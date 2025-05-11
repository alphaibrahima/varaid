<?php

namespace App\Filament\Resources\AssociationResource\Pages;

use App\Filament\Resources\AssociationResource;
use App\Models\User;
use App\Notifications\AssociationCredentialsNotification;
use App\Notifications\AccountCreatedSmsNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateAssociation extends CreateRecord
{
    protected static string $resource = AssociationResource::class;
    
    protected $password;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer le mot de passe
        $this->password = Str::random(10);
        
        return array_merge($data, [
            'password' => bcrypt($this->password),
            'role' => 'association'
        ]);
    }

    protected function afterCreate(): void
    {
        // Envoyer la notification email avec les identifiants
        $this->record->notify(
            new AssociationCredentialsNotification($this->password)
        );
        
        // Envoyer la notification SMS
        $this->record->notify(
            new AccountCreatedSmsNotification()
        );

    }
}