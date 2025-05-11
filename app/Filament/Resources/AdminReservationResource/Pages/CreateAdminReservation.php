<?php

namespace App\Filament\Resources\AdminReservationResource\Pages;

use App\Filament\Resources\AdminReservationResource;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\User;
use App\Notifications\ReservationConfirmation;
use App\Notifications\ReservationSmsNotification;
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
    
    protected function afterValidate(): void
    {
        // Vérifier la disponibilité du créneau
        $slotId = $this->data['slot_id'];
        $quantity = $this->data['quantity'];
        
        $slot = Slot::findOrFail($slotId);
        
        // Obtenir le nombre d'utilisateurs pour qui créer des réservations
        $existingUsers = $this->data['existing_users'] ?? [];
        $newUsers = $this->data['new_users'] ?? [];
        $csvFile = $this->data['csv_file'] ?? null;
        
        $totalUsers = count($existingUsers) + count($newUsers);
        
        // Si un fichier CSV est fourni, compter les lignes
        if ($csvFile) {
            $csvPath = storage_path('app/public/' . $csvFile);
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0);
            
            $records = Statement::create()->process($csv);
            $totalUsers += count($records);
        }
        
        // Vérifier qu'il y a au moins un utilisateur
        if ($totalUsers === 0) {
            $this->halt();
            Notification::make()
                ->title('Aucun utilisateur sélectionné')
                ->body('Veuillez sélectionner au moins un utilisateur pour créer des réservations.')
                ->danger()
                ->send();
            return;
        }
        
        // Vérifier que le créneau a assez de places disponibles
        $totalRequiredPlaces = $totalUsers * $quantity;
        if ($slot->available_places < $totalRequiredPlaces) {
            $this->halt();
            Notification::make()
                ->title('Pas assez de places disponibles')
                ->body("Le créneau sélectionné n'a que {$slot->available_places} places disponibles, mais vous avez besoin de {$totalRequiredPlaces} places.")
                ->danger()
                ->send();
        }
    }
    
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Ce n'est pas ici que nous allons créer les réservations
    //     // On retourne juste les données pour une utilisation dans afterCreate
    //     return $data;
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Valider les données en fonction du mode de réservation choisi
        if (!empty($data['existing_users'])) {
            // Mode utilisateurs existants
            unset($data['new_users']);
            unset($data['csv_file']);
            unset($data['csv_association_id']);
        } elseif (!empty($data['new_users'])) {
            // Mode nouveaux utilisateurs
            unset($data['existing_users']);
            unset($data['csv_file']);
            unset($data['csv_association_id']);
        } elseif (!empty($data['csv_file'])) {
            // Mode import CSV
            unset($data['existing_users']);
            unset($data['new_users']);
            
            // Vérifier que l'association est spécifiée
            if (empty($data['csv_association_id'])) {
                throw ValidationException::withMessages([
                    'csv_association_id' => 'Vous devez sélectionner une association pour les utilisateurs importés.',
                ]);
            }
        }
        
        return $data;
    }

        /**
     * Crée une réservation pour un utilisateur
     */
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
                'trace' => $e->getTraceAsString()  // Ajout de la trace pour plus de détails
            ]);
            
            // Propager l'erreur pour la traiter plus haut
            throw $e;
        }
    }
    
    protected function afterCreate(): void
    {
        // Supprimer la réservation créée automatiquement (elle n'est pas nécessaire)
        $this->record->delete();
        
        $data = $this->data;
        $slotId = $data['slot_id'];
        $slot = Slot::findOrFail($slotId);
        $quantity = $data['quantity'];
        $size = $data['size'];
        $skipSelection = $data['skip_selection'] ?? false;
        $sendNotifications = $data['send_notifications'] ?? true;
        
        // Liste pour suivre toutes les réservations créées
        $createdReservations = [];
        
        // 1. Traiter les utilisateurs existants
        if (!empty($data['existing_users'])) {
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
                    
                    $reservation = $this->createReservation($user, $slot, $quantity, $size, $skipSelection);
                    
                    if ($reservation && $sendNotifications) {
                        try {
                            // Envoyer l'email avec PDF attaché
                            $user->notify(new ReservationConfirmation($reservation, true));
                            
                            // Envoyer le SMS via Brevo
                            $user->notify(new ReservationSmsNotification($reservation, true));
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
                }
            }
        }
        
        // 2. Traiter les nouveaux utilisateurs
        if (!empty($data['new_users'])) {
            foreach ($data['new_users'] as $newUserData) {
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
                $reservation = $this->createReservation($user, $slot, $quantity, $size, $skipSelection);
                
                if ($reservation && $sendNotifications) {
                    try {
                        // Envoyer les informations de connexion
                        $user->notify(new \App\Notifications\AcheteurCredentialsNotification($password));
                        
                        // Envoyer la confirmation de réservation par email
                        $user->notify(new ReservationConfirmation($reservation, true));
                        
                        // Envoyer la confirmation par SMS
                        $user->notify(new ReservationSmsNotification($reservation, true));
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
            }
        }
        
        // 3. Traiter le fichier CSV s'il existe
        if (!empty($data['csv_file'])) {
            $csvPath = storage_path('app/public/' . $data['csv_file']);
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0);
            
            $records = Statement::create()->process($csv);
            
            foreach ($records as $record) {
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
                $reservation = $this->createReservation($user, $slot, $quantity, $size, $skipSelection);
                
                if ($reservation && $sendNotifications) {
                    try {
                        // Email de confirmation avec PDF
                        $user->notify(new ReservationConfirmation($reservation, true));
                        
                        // SMS de confirmation
                        $user->notify(new ReservationSmsNotification($reservation, true));
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de la confirmation', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if ($reservation) {
                    $createdReservations[] = $reservation;
                }
            }
            
            // Supprimer le fichier CSV temporaire
            \Illuminate\Support\Facades\Storage::disk('public')->delete($data['csv_file']);
        }
        
        // Afficher une notification de succès
        $totalReservations = count($createdReservations);
        Notification::make()
            ->title('Réservations créées avec succès')
            ->body("{$totalReservations} réservations ont été créées.")
            ->success()
            ->send();
            
        // Rediriger vers la liste des réservations
        $this->redirect(static::getResource()::getUrl('index'));
    }
    
}