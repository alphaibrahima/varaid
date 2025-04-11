<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcheteurResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AcheteurResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Acheteurs';
    protected static ?string $modelLabel = 'Acheteur';
    protected static ?string $pluralModelLabel = 'Acheteurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->columns(2)
                    ->schema([
                        TextInput::make('firstname')
                            ->label('Prénom')
                            ->required(),

                        TextInput::make('name')
                            ->label('Nom complet')
                            ->required(),
                            
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),

                Forms\Components\Section::make('Coordonnées')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('country_code')
                                    ->label('Indicatif pays')
                                    ->options(collect(config('countries.phone_codes'))->mapWithKeys(
                                        fn ($item) => [$item['code'] => "+{$item['code']} ({$item['name']})"]
                                    ))
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('phone_mask', self::getPhoneMask($state));
                                        $set('phone_example', self::getPhoneExample($state));
                                    }),

                                TextInput::make('local_phone')
                                    ->label('Numéro local')
                                    ->required()
                                    ->mask(fn (Get $get) => $get('phone_mask'))
                                    ->rules([
                                        function (Get $get) {
                                            return function ($attr, $value, $fail) use ($get) {
                                                $code = $get('country_code');
                                                $country = collect(config('countries.phone_codes'))
                                                    ->firstWhere('code', $code);
                                                
                                                if ($country && !preg_match($country['pattern'], $code . preg_replace('/\D/', '', $value))) {
                                                    $fail("Format invalide pour {$country['name']}");
                                                }
                                            };
                                        }
                                    ])
                            ]),
                            
                        Textarea::make('full_address')
                            ->label('Adresse complète')
                            ->required()
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\Hidden::make('role')
                            ->default('buyer'),
                            
                        Select::make('association_id')
                            ->label('Association affiliée')
                            ->relationship('association', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true),
                            
                        TextInput::make('password')
                            ->password()
                            ->required(fn ($operation) => $operation === 'create')
                            ->confirmed()
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                            
                        TextInput::make('password_confirmation')
                            ->password()
                            ->dehydrated(false)
                            ->visible(fn ($operation) => $operation === 'create'),
                    ]),
                    
                Forms\Components\Section::make('Affiliation')
                    ->schema([
                        Forms\Components\TextInput::make('affiliation_code')
                            ->label('Code d\'affiliation')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($operation) => $operation === 'edit'),
                            
                        Forms\Components\Toggle::make('affiliation_verified')
                            ->label('Affiliation vérifiée')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visible(fn ($operation) => $operation === 'edit'),
                            
                        Forms\Components\DateTimePicker::make('affiliation_verified_at')
                            ->label('Date de vérification')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($livewire) => $livewire->record && $livewire->record->hasVerifiedAffiliation()),
                    ])
                    ->visible(fn ($operation) => $operation === 'edit'),
            ]);
    }

    private static function getPhoneMask(?string $countryCode): ?string
    {
        $country = collect(config('countries.phone_codes'))->firstWhere('code', $countryCode);
        return $country['mask'] ?? null;
    }

    private static function getPhoneExample(?string $countryCode): ?string
    {
        $country = collect(config('countries.phone_codes'))->firstWhere('code', $countryCode);
        return $country['example'] ?? null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                    
                TextColumn::make('formatted_phone')
                    ->label('Téléphone'),
                    
                TextColumn::make('association.name')
                    ->label('Association'),
                    
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y'),

                TextColumn::make('affiliation_verified')
                    ->label('Affiliation vérifiée')
                    ->badge()
                    ->state(function (User $record): string {
                        return $record->affiliation_verified ? 'Vérifiée' : 'Non vérifiée';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Vérifiée' => 'success',
                        'Non vérifiée' => 'danger',
                    }),
                    
                TextColumn::make('affiliation_code')
                    ->label('Code d\'affiliation')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('association')
                    ->relationship('association', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),

                Action::make('regenerateCode')
                    ->label('Régénérer code')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (User $record) => Auth::user()->can('viewAffiliationCode', $record))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Checkbox::make('send_notification')
                            ->label('Envoyer une notification')
                            ->default(true),
                    ])
                    ->action(function (User $record, array $data) {
                        $code = $record->generateAffiliationCode();
                        
                        if ($data['send_notification']) {
                            $record->notify(new \App\Notifications\AffiliationCodeNotification());
                        }
                        
                        Notification::make()
                            ->title('Code régénéré')
                            ->body("Nouveau code: {$code}")
                            ->success()
                            ->send();
                    }),
                
                Action::make('verifyAffiliation')
                    ->label('Vérifier affiliation')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $record) => !$record->hasVerifiedAffiliation() && Auth::user()->can('verifyAffiliation', $record))
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->markAffiliationAsVerified();
                        
                        Notification::make()
                            ->title('Affiliation vérifiée')
                            ->success()
                            ->send();
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'buyer'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcheteurs::route('/'),
            'create' => Pages\CreateAcheteur::route('/create'),
            'edit' => Pages\EditAcheteur::route('/{record}/edit'),
        ];
    }
}