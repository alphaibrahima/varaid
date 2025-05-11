<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssociationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssociationResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Associations';
    protected static ?string $modelLabel = 'Association';
    protected static ?string $pluralModelLabel = 'Associations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom de l\'association')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->label('Adresse')
                    ->required(),
                Forms\Components\TextInput::make('registration_number')
                    ->label('Numéro d\'enregistrement'),
            ]);
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('name')->searchable(),
    //             Tables\Columns\TextColumn::make('email'),
    //             Tables\Columns\TextColumn::make('registration_number')->searchable(),
    //             Tables\Columns\IconColumn::make('is_active')->boolean(),
    //         ])
    //         ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'association'))
    //         ->filters([
    //             // 
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('N° Enregistrement')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'association'))
            ->filters([
                // 
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssociations::route('/'),
            'create' => Pages\CreateAssociation::route('/create'),
            'edit' => Pages\EditAssociation::route('/{record}/edit'),
        ];
    }
}