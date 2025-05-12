<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminReservationResource\Pages;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class AdminReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Réservations Admin';
    protected static ?string $modelLabel = 'Réservation Administrative';
    protected static ?string $pluralModelLabel = 'Réservations Administratives';
    protected static ?string $navigationGroup = 'Réservations';
    protected static ?int $navigationSort = 2;

    // protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    // protected static ?string $navigationLabel = 'Création Avancée';
    // protected static ?string $modelLabel = 'Création par Lot';
    // protected static ?string $pluralModelLabel = 'Créations par Lot';
    // protected static ?string $navigationGroup = 'Réservations';
    // protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Choix du créneau')
                        ->description('Sélectionnez le jour et l\'heure')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Forms\Components\Select::make('date')
                                ->label('Date de la réservation')
                                ->required()
                                ->options(function () {
                                    // Récupérer uniquement les dates qui ont au moins un créneau disponible
                                    return Slot::where('available', true)
                                        ->where('date', '>=', now())
                                        ->select('date')
                                        ->distinct()
                                        ->orderBy('date')
                                        ->get()
                                        ->mapWithKeys(function ($slot) {
                                            $formattedDate = Carbon::parse($slot->date)->format('d/m/Y');
                                            $dayName = Carbon::parse($slot->date)->translatedFormat('l');
                                            return [$slot->date->format('Y-m-d') => "{$formattedDate} ({$dayName})"];
                                        });
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function (Forms\Set $set) {
                                    $set('slot_id', null);
                                }),
                            
                            Forms\Components\Select::make('slot_id')
                                ->label('Créneau horaire')
                                ->options(function (callable $get) {
                                    $date = $get('date');
                                    if (!$date) return [];
                                    
                                    return Slot::where('date', $date)
                                        ->where('available', true)
                                        ->get()
                                        ->mapWithKeys(function ($slot) {
                                            $startTime = substr($slot->start_time, 0, 5);
                                            $endTime = substr($slot->end_time, 0, 5);
                                            $availablePlaces = $slot->available_places;
                                            
                                            return [$slot->id => "{$startTime} - {$endTime} ({$availablePlaces} places disponibles)"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->preload(),
                            
                            // Ajouter ces champs cachés
                            Forms\Components\Hidden::make('user_id')
                                ->default(function() {
                                    return auth()->id(); 
                                }),
                                
                            Forms\Components\Hidden::make('association_id')
                                ->default(function() {
                                    $user = auth()->user();
                                    return $user && $user->association_id ? $user->association_id : null;
                                }),
                                // ->required(), 
                                
                            Forms\Components\Hidden::make('code')
                                ->default(function() {
                                    return 'R-' . \Illuminate\Support\Str::random(6);
                                }),
                                
                            Forms\Components\Hidden::make('status')
                                ->default('confirmed'),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Mode de réservation')
                        ->description('Choisissez le mode de réservation')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Tabs::make('reservation_mode')
                                ->tabs([
                                    //
                                    // Dans l'onglet "Utilisateurs existants"
                                    Forms\Components\Tabs\Tab::make('Utilisateurs existants')
                                        ->icon('heroicon-o-users')
                                        ->schema([
                                            Forms\Components\Select::make('existing_users')
                                                ->label('Sélectionner des utilisateurs')
                                                ->multiple()
                                                ->options(function () {
                                                    return User::where('role', 'buyer')
                                                        ->where('is_active', true)
                                                        ->get()
                                                        ->mapWithKeys(function ($user) {
                                                            return [$user->id => "{$user->firstname} {$user->name} ({$user->email})"];
                                                        });
                                                })
                                                ->searchable(['name', 'firstname', 'email'])
                                                ->reactive() // Important pour déclencher la mise à jour
                                                ->required()
                                                ->preload(),
                                                
                                            // Ajout du champ association_id dynamique
                                            Forms\Components\Select::make('association_id')
                                                ->label('Association')
                                                ->options(function (callable $get) {
                                                    // Récupérer les utilisateurs sélectionnés
                                                    $selectedUsers = $get('existing_users') ?? [];
                                                    
                                                    if (empty($selectedUsers)) {
                                                        // Aucun utilisateur sélectionné, afficher toutes les associations
                                                        return User::where('role', 'association')
                                                            ->pluck('name', 'id');
                                                    } else {
                                                        // Récupérer les IDs des associations des utilisateurs sélectionnés
                                                        $associationIds = User::whereIn('id', $selectedUsers)
                                                            ->pluck('association_id')
                                                            ->filter()
                                                            ->unique();
                                                        
                                                        // Récupérer les associations correspondantes
                                                        return User::whereIn('id', $associationIds)
                                                            ->where('role', 'association')
                                                            ->pluck('name', 'id');
                                                    }
                                                })
                                                ->searchable()
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                    // Logique optionnelle après changement d'association
                                                })
                                                ->helperText('Association pour cette réservation'),
                                        ]),
                                    
                                    Forms\Components\Tabs\Tab::make('Nouveaux utilisateurs')
                                        ->icon('heroicon-o-user-plus')
                                        ->schema([
                                            Forms\Components\Repeater::make('new_users')
                                                ->schema([
                                                    Forms\Components\TextInput::make('firstname')
                                                        ->label('Prénom')
                                                        ->required(),
                                                    
                                                    Forms\Components\TextInput::make('name')
                                                        ->label('Nom')
                                                        ->required(),
                                                    
                                                    Forms\Components\TextInput::make('email')
                                                        ->label('Email')
                                                        ->email()
                                                        ->required(),
                                                    
                                                    Forms\Components\TextInput::make('phone')
                                                        ->label('Téléphone')
                                                        ->tel()
                                                        ->required(),
                                                    
                                                    Forms\Components\Textarea::make('full_address')
                                                        ->label('Adresse complète')
                                                        ->required()
                                                        ->rows(2),
                                                    //
                                                    Forms\Components\Select::make('association_id')
                                                        ->label('Association')
                                                        ->options(User::where('role', 'association')->pluck('name', 'id'))
                                                        ->required()
                                                        ->searchable(),
                                                    //
                                                ])
                                                ->columns(2)
                                                ->addActionLabel('Ajouter un utilisateur')
                                                ->itemLabel(fn (array $state): ?string => 
                                                    isset($state['firstname']) && isset($state['name']) 
                                                        ? "{$state['firstname']} {$state['name']}" 
                                                        : null
                                                ),
                                        ]),
                                    
                                    Forms\Components\Tabs\Tab::make('Import CSV')
                                        ->icon('heroicon-o-document-text')
                                        ->schema([
                                            Forms\Components\FileUpload::make('csv_file')
                                                ->label('Fichier CSV des utilisateurs')
                                                ->disk('public')
                                                ->directory('temp-imports')
                                                ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                                                ->helperText('Le fichier doit contenir les colonnes: prenom, nom, email, telephone, adresse'),
                                            //
                                            Forms\Components\Select::make('csv_association_id')
                                                ->label('Association pour tous les utilisateurs importés')
                                                ->options(User::where('role', 'association')->pluck('name', 'id'))
                                                ->required() // Assurez-vous que c'est toujours required
                                                ->searchable(),

                                            //
                                            
                                            Forms\Components\View::make('filament.resources.admin-reservation-resource.csv-template'),
                                        ]),
                                ])
                                ->activeTab(0)
                                ->columnSpanFull(),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Configuration')
                        ->description('Options de réservation')
                        ->icon('heroicon-o-cog')
                        ->schema([
                            Forms\Components\Toggle::make('skip_selection')
                                ->label('Ne pas venir choisir l\'agneau sur place')
                                ->helperText('Si activé, l\'agneau sera attribué par l\'association')
                                ->default(false),
                            
                            Forms\Components\Select::make('size')
                                ->label('Taille de l\'agneau')
                                ->options([
                                    'grand' => 'Grand (~25kg)',
                                    'moyen' => 'Moyen (~18kg)',
                                    'petit' => 'Petit (~12kg)',
                                ])
                                ->default('grand')
                                ->required(),
                            
                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantité par utilisateur')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(4)
                                ->required()
                                ->helperText('Maximum 4 agneaux par utilisateur'),
                            
                            Forms\Components\Toggle::make('send_notifications')
                                ->label('Envoyer les notifications')
                                ->helperText('Email et SMS de confirmation')
                                ->default(true),
                            
                            Forms\Components\Textarea::make('admin_notes')
                                ->label('Notes administratives')
                                ->placeholder('Notes internes (non visibles par les utilisateurs)')
                                ->rows(3),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slot.start_time')
                    ->label('Heure')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5)),
                    
                Tables\Columns\TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'grand' => 'Grand',
                        'moyen' => 'Moyen',
                        'petit' => 'Petit',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité'),
                    
                Tables\Columns\IconColumn::make('skip_selection')
                    ->label('Sélection sur place')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-mark')
                    ->falseIcon('heroicon-o-check'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'danger' => 'canceled',
                        'warning' => 'pending',
                        'success' => 'confirmed',
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmée',
                        'canceled' => 'Annulée',
                    ]),
                    
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Annuler les réservations')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'canceled']);
                            }
                            
                            Notification::make()
                                ->title(count($records) . ' réservations annulées')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }


    public static function canAccess(): bool
    {
        return true; 
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true; 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminReservations::route('/'),
            'create' => Pages\CreateAdminReservation::route('/create'), // Assurez-vous que cette ligne existe
            'edit' => Pages\EditAdminReservation::route('/{record}/edit'),
            'view' => Pages\ViewAdminReservation::route('/{record}'),
        ];
    }

    // protected function afterValidate(): void
    // {
    //     parent::afterValidate();
        
    //     // Si association_id est null, lever une exception
    //     if (is_null($this->data['association_id'])) {
    //         $this->halt();
    //         Notification::make()
    //             ->title('Association requise')
    //             ->body('Vous devez sélectionner une association pour cette réservation.')
    //             ->danger()
    //             ->send();
    //     }
    // }
}