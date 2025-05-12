<?php

namespace App\Filament\Resources\AdminReservationResource\Pages;

use App\Filament\Resources\AdminReservationResource;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\User;
use App\Notifications\ReservationConfirmation;
// use App\Notifications\ReservationSmsNotification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;

class CreateAdminReservation extends CreateRecord
{
    protected static string $resource = AdminReservationResource::class;
    
    protected function beforeCreate(): void
    {
        // Vérification additionnelle pour association_id
        if (empty($this->data['association_id'])) {
            Notification::make()
                ->title('Association requise')
                ->body('Vous devez sélectionner une association pour cette réservation.')
                ->danger()
                ->send();
                
            $this->halt();
        }
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Valider les données en fonction du mode de réservation choisi
    //     if (!empty($data['existing_users'])) {
    //         // Mode utilisateurs existants
    //         unset($data['new_users']);
    //         unset($data['csv_file']);
    //         unset($data['csv_association_id']);
    //     } elseif (!empty($data['new_users'])) {
    //         // Mode nouveaux utilisateurs
    //         unset($data['existing_users']);
    //         unset($data['csv_file']);
    //         unset($data['csv_association_id']);
    //     } elseif (!empty($data['csv_file'])) {
    //         // Mode import CSV
    //         unset($data['existing_users']);
    //         unset($data['new_users']);
            
    //         // Vérifier que l'association est spécifiée
    //         if (empty($data['csv_association_id'])) {
    //             throw ValidationException::withMessages([
    //                 'csv_association_id' => 'Vous devez sélectionner une association pour les utilisateurs importés.',
    //             ]);
    //         }
    //     }
        
    //     return $data;
    // }

    // Si vous avez besoin de manipuler les données avant création
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si association_id est toujours null à ce stade, tentative de récupération
        if (empty($data['association_id'])) {
            // Essayer de récupérer l'association à partir des utilisateurs sélectionnés
            if (!empty($data['existing_users']) && is_array($data['existing_users'])) {
                $firstUserId = $data['existing_users'][0] ?? null;
                if ($firstUserId) {
                    $user = \App\Models\User::find($firstUserId);
                    if ($user && $user->association_id) {
                        $data['association_id'] = $user->association_id;
                    }
                }
            }
        }

        return $data;
    }

    private function createReservation(User $user, Slot $slot, int $quantity, string $size, bool $skipSelection): ?Reservation
    {
        try {
            // Vérifier que l'ID d'association existe
            if (!$user->association_id) {
                Log::error('Utilisateur sans association', ['user_id' => $user->id]);
                
                // Utiliser une association par défaut ou lever une exception
                throw new \Exception("L'utilisateur n'a pas d'association associée");
            }
            
            // Générer un code unique pour la réservation
            $code = 'R-' . Str::random(8);
            
            // Créer la réservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'association_id' => $user->association_id,
                'size' => $size,
                'quantity' => $quantity,
                'code' => $code,
                'status' => 'confirmed',
                'date' => $slot->date,
                'skip_selection' => $skipSelection,
                'payment_intent_id' => 'admin-created-' . time() . '-' . Str::random(5),
            ]);
            
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la réservation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Propager l'erreur pour la traiter plus haut
            throw $e;
        }
    }
    
    protected function afterCreate(): void
    {
        // On évite de supprimer la réservation créée automatiquement
        // Car cela cause les problèmes de clé étrangère

        $data = $this->data;
        $slotId = $data['slot_id'];
        $slot = Slot::findOrFail($slotId);
        $quantity = $data['quantity'];
        $size = $data['size'];
        $skipSelection = $data['skip_selection'] ?? false;
        $sendNotifications = $data['send_notifications'] ?? true;
        
        // Liste pour suivre toutes les réservations créées
        $createdReservations = [$this->record];
        
        // 1. Traiter les utilisateurs existants
        if (!empty($data['existing_users'])) {
            $isFirst = true;
            
            foreach ($data['existing_users'] as $userId) {
                try {
                    $user = User::findOrFail($userId);
                    
                    // Vérifier que l'utilisateur a une association
                    if (!$user->association_id) {
                        Notification::make()
                            ->title('Association manquante')
                            ->body("L'utilisateur {$user->name} n'a pas d'association associée.")
                            ->danger()
                            ->send();
                        continue;  // Passer à l'utilisateur suivant
                    }
                    
                    if ($isFirst) {
                        // Mettre à jour la réservation existante au lieu de la supprimer
                        $this->record->update([
                            'user_id' => $user->id,
                            'association_id' => $user->association_id,
                            'slot_id' => $slot->id,
                            'size' => $size,
                            'quantity' => $quantity,
                            'status' => 'confirmed',
                            'date' => $slot->date,
                            'skip_selection' => $skipSelection,
                            'payment_intent_id' => 'admin-created-' . time() . '-' . Str::random(5),
                        ]);
                        
                        $reservation = $this->record;
                        $isFirst = false;
                    } else {
                        // Créer de nouvelles réservations pour les autres utilisateurs
                        $reservation = Reservation::create([
                            'user_id' => $user->id,
                            'slot_id' => $slot->id,
                            'association_id' => $user->association_id,
                            'size' => $size,
                            'quantity' => $quantity,
                            'code' => 'R-' . Str::random(8),
                            'status' => 'confirmed',
                            'date' => $slot->date,
                            'skip_selection' => $skipSelection,
                            'payment_intent_id' => 'admin-created-' . time() . '-' . Str::random(5),
                        ]);
                    }
                    
                    if ($reservation && $sendNotifications) {
                        try {
                            // Envoyer l'email avec PDF attaché
                            $user->notify(new ReservationConfirmation($reservation, true));
                            
                            // Envoyer le SMS via Brevo
                            // $user->notify(new ReservationSmsNotification($reservation, true));
                        } catch (\Exception $e) {
                            Log::error('Erreur lors de l\'envoi des notifications', [
                                'user_id' => $user->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    if ($reservation) {
                        $createdReservations[] = $reservation;
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body("Impossible de créer la réservation pour l'utilisateur ID {$userId}: " . $e->getMessage())
                        ->danger()
                        ->send();
                        
                    Log::error('Erreur lors de la création de la réservation', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
        
        // 2. Traiter les nouveaux utilisateurs
        if (!empty($data['new_users'])) {
            foreach ($data['new_users'] as $newUserData) {
                try {
                    // Créer le nouvel utilisateur
                    $password = Str::random(10);
                    
                    $user = User::create([
                        'firstname' => $newUserData['firstname'],
                        'name' => $newUserData['name'],
                        'email' => $newUserData['email'],
                        'phone' => $newUserData['phone'],
                        'full_address' => $newUserData['full_address'],
                        'association_id' => $newUserData['association_id'],
                        'password' => Hash::make($password),
                        'role' => 'buyer',
                        'is_active' => true,
                    ]);
                    
                    // Créer la réservation pour ce nouvel utilisateur
                    $reservation = Reservation::create([
                        'user_id' => $user->id,
                        'slot_id' => $slot->id,
                        'association_id' => $user->association_id,
                        'size' => $size,
                        'quantity' => $quantity,
                        'code' => 'R-' . Str::random(8),
                        'status' => 'confirmed',
                        'date' => $slot->date,
                        'skip_selection' => $skipSelection,
                        'payment_intent_id' => 'admin-created-' . time() . '-' . Str::random(5),
                    ]);
                    
                    if ($reservation && $sendNotifications) {
                        try {
                            // Envoyer les informations de connexion
                            $user->notify(new \App\Notifications\AcheteurCredentialsNotification($password));
                            
                            // Envoyer la confirmation de réservation par email
                            $user->notify(new ReservationConfirmation($reservation, true));
                            
                            // Envoyer la confirmation par SMS
                            // $user->notify(new ReservationSmsNotification($reservation, true));
                        } catch (\Exception $e) {
                            Log::error('Erreur lors de l\'envoi des notifications', [
                                'user_id' => $user->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    if ($reservation) {
                        $createdReservations[] = $reservation;
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body("Impossible de créer l'utilisateur ou sa réservation: " . $e->getMessage())
                        ->danger()
                        ->send();
                        
                    Log::error('Erreur lors de la création de l\'utilisateur ou de sa réservation', [
                        'user_data' => $newUserData,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
        
        // 3. Traiter le fichier CSV s'il existe
        if (!empty($data['csv_file'])) {
            try {
                $csvPath = storage_path('app/public/' . $data['csv_file']);
                $csv = Reader::createFromPath($csvPath, 'r');
                $csv->setHeaderOffset(0);
                
                $records = Statement::create()->process($csv);
                $csvUsersProcessed = 0;
                
                foreach ($records as $record) {
                    try {
                        // Vérifier si un utilisateur existe déjà avec cet email
                        $existingUser = User::where('email', $record['email'])->first();
                        
                        if ($existingUser) {
                            $user = $existingUser;
                        } else {
                            // Créer un nouvel utilisateur
                            $password = Str::random(10);
                            
                            $user = User::create([
                                'firstname' => $record['prenom'],
                                'name' => $record['nom'],
                                'email' => $record['email'],
                                'phone' => $record['telephone'],
                                'full_address' => $record['adresse'],
                                'association_id' => $data['csv_association_id'],
                                'password' => Hash::make($password),
                                'role' => 'buyer',
                                'is_active' => true,
                            ]);
                            
                            // Envoyer les informations de connexion au nouvel utilisateur
                            if ($sendNotifications) {
                                try {
                                    $user->notify(new \App\Notifications\AcheteurCredentialsNotification($password));
                                } catch (\Exception $e) {
                                    Log::error('Erreur lors de l\'envoi des identifiants', [
                                        'user_id' => $user->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                        
                        // Créer la réservation
                        $reservation = Reservation::create([
                            'user_id' => $user->id,
                            'slot_id' => $slot->id,
                            'association_id' => $user->association_id,
                            'size' => $size,
                            'quantity' => $quantity,
                            'code' => 'R-' . Str::random(8),
                            'status' => 'confirmed',
                            'date' => $slot->date,
                            'skip_selection' => $skipSelection,
                            'payment_intent_id' => 'admin-created-' . time() . '-' . Str::random(5),
                        ]);
                        
                        if ($reservation && $sendNotifications) {
                            try {
                                // Email de confirmation avec PDF
                                $user->notify(new ReservationConfirmation($reservation, true));
                                
                                // SMS de confirmation
                                // $user->notify(new ReservationSmsNotification($reservation, true));
                            } catch (\Exception $e) {
                                Log::error('Erreur lors de l\'envoi de la confirmation', [
                                    'user_id' => $user->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        if ($reservation) {
                            $createdReservations[] = $reservation;
                            $csvUsersProcessed++;
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur lors du traitement d\'une ligne CSV', [
                            'record' => $record,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if ($csvUsersProcessed > 0) {
                    Notification::make()
                        ->title('Import CSV réussi')
                        ->body("$csvUsersProcessed utilisateurs importés et réservations créées.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Import CSV')
                        ->body("Aucun utilisateur n'a été importé depuis le CSV.")
                        ->warning()
                        ->send();
                }
                
                // Supprimer le fichier CSV temporaire
                \Illuminate\Support\Facades\Storage::disk('public')->delete($data['csv_file']);
                
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Erreur lors de l\'import CSV')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
                    
                Log::error('Erreur lors de l\'import CSV', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Afficher une notification de succès
        $totalReservations = count($createdReservations) - 1; // On ne compte pas la réservation initiale si elle a été mise à jour
        if ($totalReservations > 0) {
            Notification::make()
                ->title('Réservations créées avec succès')
                ->body("{$totalReservations} réservations ont été créées.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Aucune réservation créée')
                ->body("Vérifiez vos critères de sélection.")
                ->warning()
                ->send();
        }
        
        // Rediriger vers la liste des réservations
        $this->redirect(static::getResource()::getUrl('index'));
    }
}