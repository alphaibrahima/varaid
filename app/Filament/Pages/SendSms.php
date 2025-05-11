<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SmsMessageResource;
use App\Models\SmsMessage;
use App\Models\User;
use App\Services\BrevoSmsService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendSms extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Envoyer SMS';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?string $title = 'Envoyer des SMS';
    protected static ?string $slug = 'send-sms';
    protected static string $view = 'filament.pages.send-sms';
    
    public ?array $data = [];
    public bool $showResults = false;
    public array $results = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('Message')
                    ->required()
                    ->helperText('Le message ne doit pas dépasser 160 caractères.')
                    ->maxLength(160)
                    ->rows(3)
                    ->columnSpanFull(),
                
                Select::make('recipient_type')
                    ->label('Type de destinataires')
                    ->options([
                        'all' => 'Tous les acheteurs',
                        'specific' => 'Acheteurs spécifiques',
                        'filtered' => 'Acheteurs filtrés',
                    ])
                    ->default('all')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('recipients', null)),
                
                CheckboxList::make('recipients')
                    ->label('Destinataires')
                    ->options(function (callable $get) {
                        if ($get('recipient_type') === 'specific') {
                            return User::where('role', 'buyer')
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                        return [];
                    })
                    ->hidden(fn (callable $get) => $get('recipient_type') !== 'specific')
                    ->columns(2)
                    ->required(fn (callable $get) => $get('recipient_type') === 'specific'),
                
                Select::make('filter_by')
                    ->label('Filtrer par')
                    ->options([
                        'association' => 'Association',
                        'has_reservations' => 'Avec réservations',
                        'no_reservations' => 'Sans réservations',
                    ])
                    ->hidden(fn (callable $get) => $get('recipient_type') !== 'filtered')
                    ->reactive(),
                
                Select::make('association_id')
                    ->label('Association')
                    ->options(function () {
                        return User::where('role', 'association')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->hidden(fn (callable $get) => 
                        $get('recipient_type') !== 'filtered' || 
                        $get('filter_by') !== 'association'
                    ),

                Select::make('test_mode')
                    ->label('Mode Test')
                    ->options([
                        'false' => 'Envoi réel',
                        'true' => 'Mode test (sans envoi)',
                    ])
                    ->default('false')
                    ->required()
                    ->helperText('En mode test, aucun SMS ne sera réellement envoyé.'),
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        
        try {
            // Récupérer les destinataires selon les critères
            $usersQuery = User::where('role', 'buyer')
                ->where('is_active', true)
                ->whereNotNull('phone');
            
            switch ($data['recipient_type']) {
                case 'all':
                    // Tous les acheteurs, aucun filtre supplémentaire
                    break;
                    
                case 'specific':
                    // Acheteurs spécifiques
                    if (!empty($data['recipients'])) {
                        $usersQuery->whereIn('id', $data['recipients']);
                    } else {
                        Notification::make()
                            ->title('Aucun destinataire sélectionné')
                            ->warning()
                            ->send();
                        return;
                    }
                    break;
                    
                case 'filtered':
                    // Filtrage par critères
                    if ($data['filter_by'] === 'association' && isset($data['association_id'])) {
                        $usersQuery->where('association_id', $data['association_id']);
                    } elseif ($data['filter_by'] === 'has_reservations') {
                        $usersQuery->whereHas('reservations');
                    } elseif ($data['filter_by'] === 'no_reservations') {
                        $usersQuery->whereDoesntHave('reservations');
                    }
                    break;
            }
            
            $users = $usersQuery->get(['id', 'name', 'phone']);
            
            if ($users->isEmpty()) {
                Notification::make()
                    ->title('Aucun destinataire trouvé')
                    ->warning()
                    ->send();
                return;
            }
            
            // Récupérer les destinataires
            $recipients = $users->pluck('phone', 'id')->toArray();
            
            // Mode test ou envoi réel
            $testMode = $data['test_mode'] === 'true';
            $message = $data['message'];
            
            // Créer un enregistrement pour le SMS
            $smsRecord = SmsMessage::create([
                'message' => $message,
                'recipients_count' => count($recipients),
                'status' => $testMode ? 'test' : 'pending',
                'sender_id' => Auth::id(),
            ]);
            
            // Si mode test, simuler l'envoi
            if ($testMode) {
                $this->results = [
                    'success' => true,
                    'message' => 'Mode test - Aucun SMS envoyé',
                    'recipients' => $users->pluck('name', 'id')->toArray(),
                    'recipients_count' => $users->count(),
                    'success_count' => 0,
                    'error_count' => 0,
                ];
                
                $smsRecord->update([
                    'status' => 'test',
                    'response' => $this->results,
                ]);
                
                $this->showResults = true;
                
                Notification::make()
                    ->title('Test réussi!')
                    ->body('Mode test activé - Aucun SMS n\'a été envoyé')
                    ->success()
                    ->send();
                    
                return;
            }
            
            // Envoi réel
            $smsService = app(BrevoSmsService::class);
            $results = [
                'success' => true,
                'message' => 'Envoi des SMS en cours',
                'recipients' => $users->pluck('name', 'id')->toArray(),
                'recipients_count' => count($recipients),
                'success_count' => 0,
                'error_count' => 0,
                'details' => []
            ];
            
            foreach ($recipients as $userId => $phone) {
                try {
                    $success = $smsService->sendSMS($phone, $message);
                    
                    if ($success) {
                        $results['success_count']++;
                        $results['details'][$userId] = ['success' => true];
                    } else {
                        $results['error_count']++;
                        $results['details'][$userId] = ['success' => false, 'error' => 'Échec de l\'envoi'];
                    }
                    
                    // Petite pause pour éviter de surcharger l'API
                    usleep(100000); // 100ms
                } catch (\Exception $e) {
                    $results['error_count']++;
                    $results['details'][$userId] = [
                        'success' => false, 
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage(), [
                        'user_id' => $userId,
                        'phone' => $phone
                    ]);
                }
            }
            
            // Mise à jour du statut final
            $status = $results['error_count'] === 0 ? 'completed' : 
                     ($results['success_count'] > 0 ? 'partial' : 'failed');
            
            $smsRecord->update([
                'status' => $status,
                'response' => $results,
            ]);
            
            $this->results = $results;
            $this->showResults = true;
            
            Notification::make()
                ->title('SMS envoyés!')
                ->body("Résultat: {$results['success_count']} succès, {$results['error_count']} échecs")
                ->success()
                ->send();
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des SMS: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}