<?php

namespace App\Filament\Resources;

use App\Models\SmsMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SmsMessageResource\Pages;

class SmsMessageResource extends Resource
{
    protected static ?string $model = SmsMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Historique SMS';
    
    protected static ?string $navigationGroup = 'Communication';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->disabled(),
                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->disabled()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('recipients_count')
                    ->label('Nombre de destinataires')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Statut')
                    ->disabled(),
                Forms\Components\Textarea::make('response')
                    ->label('Détails techniques')
                    ->disabled()
                    ->columnSpanFull()
                    ->rows(10)
                    ->hidden(fn($record) => !app()->isLocal()),
                Forms\Components\TextInput::make('sender.name')
                    ->label('Envoyé par')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Date d\'envoi')
                    ->displayFormat('d/m/Y H:i:s')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'envoi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('recipients_count')
                    ->label('Destinataires')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'partial' => 'warning',
                        'failed' => 'danger',
                        'test' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Envoyé par'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'completed' => 'Réussi',
                        'partial' => 'Partiellement réussi',
                        'failed' => 'Échoué',
                        'test' => 'Test',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Pas d'actions groupées
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    //
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsMessages::route('/'),
            'view' => Pages\ViewSmsMessage::route('/{record}'),
        ];
    }   
}