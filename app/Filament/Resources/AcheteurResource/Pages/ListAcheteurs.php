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
use Illuminate\Support\Facades\Log;

class ListAcheteurs extends ListRecords
{
    protected static string $resource = AcheteurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvel Acheteur'),
            //
                
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
                        
                        // Récupérer les statistiques
                        $stats = $import->getStats();
                        $passwords = $import->getPasswords();
                        
                        // Préparer le récapitulatif
                        $association = User::find($data['association_id']);
                        $recap = "Importation terminée\n";
                        $recap .= "- {$stats['imported']} acheteur(s) importé(s) sur {$stats['total']}\n";
                        $recap .= "- {$stats['skipped']} ligne(s) ignorée(s) ou en erreur\n";
                        $recap .= "- Association assignée: {$association->name}\n";
                        
                        // Préparer le détail des erreurs pour affichage
                        $errorDetails = '';
                        if (count($stats['errors']) > 0) {
                            $errorCount = min(count($stats['errors']), 5);
                            $errorDetails = implode("\n", array_slice($stats['errors'], 0, $errorCount));
                            if (count($stats['errors']) > 5) {
                                $errorDetails .= "\n...et " . (count($stats['errors']) - 5) . " autres erreurs";
                            }
                        }
                        
                        if ($stats['imported'] > 0 && !$data['send_emails']) {
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
                            
                            // Si des erreurs sont présentes
                            if (!empty($errorDetails)) {
                                Notification::make()
                                    ->title('Importation partielle')
                                    ->warning()
                                    ->body($recap . "\n\nErreurs rencontrées:\n" . $errorDetails)
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
                            }
                        } else {
                            // Si aucun acheteur importé ou emails envoyés
                            if ($stats['imported'] > 0) {
                                Notification::make()
                                    ->title('Importation réussie')
                                    ->success()
                                    ->body($recap . (!empty($errorDetails) ? "\n\nErreurs rencontrées:\n" . $errorDetails : ""))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Aucun acheteur importé')
                                    ->warning()
                                    ->body($recap . "\n\nErreurs rencontrées:\n" . $errorDetails)
                                    ->send();
                            }
                        }
                        
                        // Supprimer le fichier temporaire
                        Storage::disk('public')->delete($data['file']);
                        
                    } catch (\Exception $e) {
                        // Gestion des erreurs générales
                        Notification::make()
                            ->title('Erreur')
                            ->body('Une erreur est survenue lors de l\'importation: ' . $e->getMessage())
                            ->danger()
                            ->send();
                            
                        Log::error('Erreur d\'importation: ' . $e->getMessage(), [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }),
        ];
    }
}