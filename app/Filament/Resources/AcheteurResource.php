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

                TextColumn::make('firstname')
                    ->label('Prénom')
                    ->searchable(),

                TextColumn::make('email') 
                    ->label('Email')
                    ->searchable(),
                
                TextColumn::make('formatted_phone')
                    ->label('Téléphone'),
                    
                TextColumn::make('phone')  
                    ->label('Téléphone brut')
                    ->searchable()
                    ->hidden(), 
                    
                TextColumn::make('association.name')
                    ->label('Association'),
                    
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y'),
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
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'buyer'))
            ->searchable();
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