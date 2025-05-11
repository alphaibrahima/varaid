<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Filters\OwnerInfoFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Réservations';
    protected static ?string $modelLabel = 'Réservation';
    protected static ?string $pluralModelLabel = 'Réservations';
    protected static ?int $navigationSort = 3; // Position dans le menu de navigation

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la réservation')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                            
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'confirmed' => 'Confirmé',
                                'canceled' => 'Annulé'
                            ])
                            ->required(),
                            
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required(),
                            
                        Forms\Components\Select::make('slot_id')
                            ->label('Créneau')
                            ->relationship('slot', 'start_time')
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->start_time . ' - ' . $record->end_time)
                            ->required(),
                            
                        Forms\Components\Select::make('size')
                            ->label('Taille')
                            ->options([
                                'grand' => 'Grand',
                                'moyen' => 'Moyen',
                                'petit' => 'Petit'
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(4),
                            
                        Forms\Components\Toggle::make('skip_selection')
                            ->label('Ne pas venir choisir')
                            ->helperText('Si activé, l\'agneau sera attribué par l\'association')
                            ->default(false),
                            
                        Forms\Components\TextInput::make('payment_intent_id')
                            ->label('ID de paiement')
                            ->helperText('Référence du paiement')
                            ->disabled(),
                    ]),
                    
                Forms\Components\Section::make('Acheteur')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Acheteur')
                            ->relationship('user', 'name')
                            ->searchable(['name', 'firstname', 'email', 'phone'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required(),
                                Forms\Components\TextInput::make('firstname')
                                    ->label('Prénom')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->required(),
                                Forms\Components\Hidden::make('role')
                                    ->default('buyer'),
                                Forms\Components\Hidden::make('password')
                                    ->default(fn () => bcrypt(uniqid())),
                            ]),
                            
                        Forms\Components\Select::make('association_id')
                            ->label('Association')
                            ->relationship('association', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    
                Forms\Components\Section::make('Propriétaires')
                    ->schema([
                        Forms\Components\Repeater::make('owners_data')
                            ->label('Informations des propriétaires')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\TextInput::make('firstname')
                                    ->label('Prénom')
                                    ->required(),
                                Forms\Components\TextInput::make('lastname')
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
                                Forms\Components\Textarea::make('address')
                                    ->label('Adresse')
                                    ->rows(2),
                            ])
                            ->minItems(1)
                            ->maxItems(function (Forms\Get $get) {
                                return max(1, $get('quantity'));
                            })
                            ->columns(2)
                            ->grid(2)
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slot.start_time')
                    ->label('Heure')
                    ->formatStateUsing(fn ($state, $record) => $record->slot ? substr($record->slot->start_time, 0, 5) : 'N/A')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Acheteur')
                    ->description(fn ($record) => $record->user?->firstname)
                    ->searchable(['users.name', 'users.firstname'])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('association.name')
                    ->label('Association')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('size')
                    ->label('Taille')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'grand' => 'primary',
                        'moyen' => 'success',
                        'petit' => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('skip_selection')
                    ->label('Sélection')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-mark')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (bool $state): string => $state ? 'Ne vient pas choisir' : 'Vient choisir')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'canceled' => 'Annulé',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                OwnerInfoFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'canceled' => 'Annulé',
                    ]),
                    
                Tables\Filters\SelectFilter::make('size')
                    ->label('Taille')
                    ->options([
                        'grand' => 'Grand',
                        'moyen' => 'Moyen',
                        'petit' => 'Petit',
                    ]),
                    
                Tables\Filters\SelectFilter::make('association_id')
                    ->label('Association')
                    ->relationship('association', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('date')
                    ->label('Date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                    
                Tables\Filters\Filter::make('quantity')
                    ->label('Quantité')
                    ->form([
                        Forms\Components\TextInput::make('quantity_min')
                            ->label('Minimum')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('quantity_max')
                            ->label('Maximum')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['quantity_min'],
                                fn (Builder $query, $qty): Builder => $query->where('quantity', '>=', $qty),
                            )
                            ->when(
                                $data['quantity_max'],
                                fn (Builder $query, $qty): Builder => $query->where('quantity', '<=', $qty),
                            );
                    }),
                    
                Tables\Filters\Filter::make('owner_name')
                    ->label('Nom du propriétaire')
                    ->form([
                        Forms\Components\TextInput::make('owner_name')
                            ->label('Nom/Prénom du propriétaire')
                            ->helperText('Recherche dans les données des propriétaires'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['owner_name'],
                            function (Builder $query, $name) {
                                // Recherche dans le JSON des propriétaires
                                return $query->where(function ($query) use ($name) {
                                    $query->whereJsonContains('owners_data', ['firstname' => $name])
                                          ->orWhereJsonContains('owners_data', ['lastname' => $name])
                                          // Recherche partielle dans le JSON - moins efficace mais plus flexible
                                          ->orWhereRaw('JSON_SEARCH(LOWER(owners_data), "one", LOWER(?)) IS NOT NULL', ["%{$name}%"]);
                                });
                            }
                        );
                    }),
                    
                Tables\Filters\Filter::make('skip_selection')
                    ->label('Type de sélection')
                    ->form([
                        Forms\Components\Select::make('skip_selection')
                            ->label('Type de sélection')
                            ->options([
                                '1' => 'Ne vient pas choisir',
                                '0' => 'Vient choisir',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['skip_selection']) && $data['skip_selection'] !== '',
                            fn (Builder $query): Builder => $query->where('skip_selection', $data['skip_selection']),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                // Action pour télécharger le reçu
                Tables\Actions\Action::make('download_receipt')
                    ->label('Reçu PDF')
                    ->icon('heroicon-o-document-download')
                    ->url(fn (Reservation $record): string => route('reservation.receipt.download', ['code' => $record->code]))
                    ->openUrlInNewTab(),
                
                // Action pour changer le statut
                Tables\Actions\Action::make('change_status')
                    ->label('Changer statut')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Nouveau statut')
                            ->options([
                                'pending' => 'En attente',
                                'confirmed' => 'Confirmé',
                                'canceled' => 'Annulé',
                            ])
                            ->required(),
                    ])
                    ->action(function (Reservation $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                        ]);
                        
                        // Notification pour confirmer le changement
                        \Filament\Notifications\Notification::make()
                            ->title('Statut mis à jour')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Action en masse pour changer le statut
                    Tables\Actions\BulkAction::make('bulk_change_status')
                        ->label('Changer statut')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nouveau statut')
                                ->options([
                                    'pending' => 'En attente',
                                    'confirmed' => 'Confirmé',
                                    'canceled' => 'Annulé',
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'status' => $data['status'],
                                ]);
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Statuts mis à jour')
                                ->body(count($records) . ' réservations ont été mises à jour')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    

    // À ajouter dans la classe ReservationResource
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['user', 'association', 'slot']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'code',
            'user.name',
            'user.firstname',
            'user.email',
            'user.phone',
            'association.name',
            'payment_intent_id',
        ];
    }

    // Pour la recherche dans le JSON des propriétaires
    protected static function applySearchToTableQuery(Builder $query, string $search): Builder
    {
        if (strlen($search) === 0) {
            return $query;
        }

        // Recherche standard
        $query->where(function(Builder $query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('firstname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('association', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orWhere('payment_intent_id', 'like', "%{$search}%");
            
            // Pour PostgreSQL
            if (config('database.default') === 'pgsql') {
                $query->orWhereRaw("owners_data::text ILIKE ?", ["%{$search}%"]);
            } 
            // Pour MySQL
            else {
                $query->orWhereRaw("JSON_SEARCH(LOWER(owners_data), 'one', LOWER(?)) IS NOT NULL", ["%{$search}%"]);
            }
        });

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'association', 'slot']);
        
        // Si l'utilisateur est une association, ne montrer que ses réservations
        $user = auth()->user();
        if ($user && $user->role === 'association') {
            $query->where('association_id', $user->id);
        }
        
        return $query;
    }
}