<?php

namespace App\Filament\Resources;

use App\Models\Quota;
use Filament\Forms;
use Filament\Tables;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use App\Filament\Resources\QuotaResource\Pages;

class QuotaResource extends Resource
{
    protected static ?string $model = Quota::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('association_id')
                    ->label('Association')
                    ->options(User::where('role', 'association')->pluck('name', 'id'))
                    ->required(),

                Forms\Components\TextInput::make('quantite')
                    ->label('Quantité')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('grand')
                            ->label('Grand')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('moyen')
                            ->label('Moyen')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('petit')
                            ->label('Petit')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('association.name')->label('Association'),
                Tables\Columns\TextColumn::make('quantite')->label('Quantité'),
                Tables\Columns\TextColumn::make('grand')
                    ->label('Grand')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                Tables\Columns\TextColumn::make('moyen')
                    ->label('Moyen')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                Tables\Columns\TextColumn::make('petit')
                    ->label('Petit')
                    ->formatStateUsing(fn ($state) => $state ?? 0),
            ]);
    }

    public static function afterCreate($record)
    {
        if (!$record) {
            // Si la création a échoué, rediriger vers la liste des quotas
            return redirect()->route('filament.admin.resources.quotas.index');
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotas::route('/'),
            'create' => Pages\CreateQuota::route('/create'),
            'edit' => Pages\EditQuota::route('/{record}/edit'),
        ];
    }
}
