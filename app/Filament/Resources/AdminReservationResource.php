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
    protected static ?int $navigationSort = 3;

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
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Mode de réservation')
                        ->description('Choisissez le mode de réservation')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Tabs::make('reservation_mode')
                                ->tabs([
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
                                                ->required()
                                                ->preload(),
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
                                                    
                                                    Forms\Components\Select::make('association_id')
                                                        ->label('Association')
                                                        ->options(User::where('role', 'association')->pluck('name', 'id'))
                                                        ->required()
                                                        ->searchable(),
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
                                            
                                            Forms\Components\Select::make('csv_association_id')
                                                ->label('Association pour tous les utilisateurs importés')
                                                ->options(User::where('role', 'association')->pluck('name', 'id'))
                                                ->required(fn (callable $get) => filled($get('csv_file')))
                                                ->searchable()
                                                ->visible(fn (callable $get) => filled($get('csv_file'))),
                                            
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminReservations::route('/'),
            'create' => Pages\CreateAdminReservation::route('/create'),
            'edit' => Pages\EditAdminReservation::route('/{record}/edit'),
            'view' => Pages\ViewAdminReservation::route('/{record}'),
        ];
    }
}