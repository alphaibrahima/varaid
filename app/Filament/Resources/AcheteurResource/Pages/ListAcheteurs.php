<?php

namespace App\Filament\Resources\AcheteurResource\Pages;

use App\Filament\Resources\AcheteurResource;
use App\Models\User;
use App\Imports\BuyersImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListAcheteurs extends ListRecords
{
    protected static string $resource = AcheteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvel Acheteur'),
                
            Actions\Action::make('import')
                ->label('Importer des acheteurs')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Fichier CSV/Excel')
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                        ->required()
                        ->helperText('Téléchargez un fichier au format CSV ou Excel (.xlsx, .xls)'),
                        
                    Select::make('association_id')
                        ->label('Association')
                        ->options(User::where('role', 'association')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Tous les acheteurs importés seront liés à cette association'),
                        
                    Toggle::make('send_emails')
                        ->label('Envoyer les identifiants par email')
                        ->default(false)
                        ->helperText('Si activé, chaque nouvel acheteur recevra un email avec ses identifiants de connexion'),
                        
                    Forms\Components\Section::make('Format du fichier')
                        ->collapsible()
                        ->description('Le fichier doit contenir les colonnes suivantes : nom, prenom, email, telephone, adresse_complete')
                        ->schema([
                            Forms\Components\ViewField::make('template')
                                ->view('filament.resources.acheteur-resource.import-template'),
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        // Validation du fichier
                        if (!isset($data['file'])) {
                            Notification::make()
                                ->title('Erreur')
                                ->body('Aucun fichier n\'a été téléchargé')
                                ->danger()
                                ->send();
                            return;
                        }
                
                        // Récupérer le chemin du fichier téléchargé
                        $filePath = Storage::disk('public')->path($data['file']);
                        
                        // Créer l'instance d'importation
                        $import = new BuyersImport(
                            $data['association_id'],
                            $data['send_emails'] ?? false
                        );
                        
                        // Importer le fichier
                        Excel::import($import, $filePath);
                        
                        // Récupérer les mots de passe générés
                        $passwords = $import->getPasswords();
                        $importedCount = count($passwords);
                        
                        // Préparer le récapitulatif
                        $association = User::find($data['association_id']);
                        $recap = "Importation réussie\n";
                        $recap .= "- {$importedCount} acheteur(s) importé(s)\n";
                        $recap .= "- Association assignée: {$association->name}\n";
                        $recap .= "- Envoi d'emails: " . ($data['send_emails'] ? 'Oui' : 'Non');
                        
                        if (!$data['send_emails'] && $importedCount > 0) {
                            // Créer un tableau des identifiants pour affichage
                            $userCredentials = [];
                            $users = User::whereIn('id', array_keys($passwords))->get();
                            
                            foreach ($users as $user) {
                                $userCredentials[] = [
                                    'Nom' => $user->name,
                                    'Prénom' => $user->firstname,
                                    'Email' => $user->email,
                                    'Mot de passe' => $passwords[$user->id]
                                ];
                            }
                            
                            // Afficher un récapitulatif détaillé
                            Notification::make()
                                ->title('Importation réussie')
                                ->success()
                                ->body($recap)
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('voir_details')
                                        ->label('Voir les identifiants')
                                        ->color('success')
                                        ->button()
                                        ->close()
                                        ->modalHeading('Identifiants des acheteurs importés')
                                        ->modalContent(view('filament.resources.acheteur-resource.import-results', [
                                            'userCredentials' => $userCredentials
                                        ]))
                                ])
                                ->send();
                        } else {
                            // Afficher une notification simple
                            Notification::make()
                                ->title('Importation réussie')
                                ->body($recap)
                                ->success()
                                ->send();
                        }
                        
                        // Supprimer le fichier temporaire
                        Storage::disk('public')->delete($data['file']);
                        
                    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                        // Gestion des erreurs de validation
                        $failures = $e->failures();
                        $errorMessages = [];
                        
                        foreach ($failures as $failure) {
                            $errorMessages[] = "Ligne {$failure->row()}: {$failure->errors()[0]}";
                        }
                        
                        Notification::make()
                            ->title('Erreur de validation')
                            ->body(implode("\n", array_slice($errorMessages, 0, 5)) . 
                                  (count($errorMessages) > 5 ? "\n...et " . (count($errorMessages) - 5) . " autres erreurs" : ""))
                            ->danger()
                            ->send();
                            
                    } catch (\Exception $e) {
                        // Gestion des erreurs générales
                        Notification::make()
                            ->title('Erreur')
                            ->body('Une erreur est survenue lors de l\'importation: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}