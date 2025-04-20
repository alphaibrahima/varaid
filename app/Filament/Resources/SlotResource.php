<?php

namespace App\Filament\Resources;

use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SlotResource\Pages;

class SlotResource extends Resource
{
    protected static ?string $model = Slot::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Créneaux Horaires';
    protected static ?string $modelLabel = 'Créneau';
    protected static ?string $pluralModelLabel = 'Créneaux';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Date et heures
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->minDate(now()->startOfDay()),

                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->displayFormat('H:i'),

                Forms\Components\TimePicker::make('end_time')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->after('start_time')
                    ->displayFormat('H:i'),

                // Configuration des créneaux
                Forms\Components\Fieldset::make('Configuration des créneaux')
                    ->schema([
                        Forms\Components\TextInput::make('max_reservations')
                            ->label('Nombre maximum de réservations')
                            ->numeric()
                            ->default(50)
                            ->required(),

                        Forms\Components\Select::make('duration')
                            ->label('Durée des sous-créneaux')
                            ->options([
                                30 => '30 minutes',
                                60 => '1 heure',
                                90 => '1h30',
                                120 => '2 heures'
                            ])
                            ->default(60)
                            ->required(),
                    ]),

                // Statut de disponibilité
                Forms\Components\Fieldset::make('Statut du créneau')
                    ->schema([
                        Forms\Components\Toggle::make('available')
                            ->label('Créneau disponible')
                            ->default(true)
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('block_reason')
                            ->label('Raison du blocage')
                            ->placeholder('Indiquez la raison pour laquelle ce créneau est bloqué')
                            ->helperText('Ce champ est visible uniquement lorsque le créneau est bloqué')
                            ->visible(fn (callable $get) => !$get('available'))
                            ->columnSpanFull(),
                    ]),

                // Bouton de génération
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('generateSlots')
                        ->label('Générer les créneaux')
                        ->icon('heroicon-m-plus')
                        ->action(function ($livewire) {
                            $data = $livewire->form->getState();
                            
                            // Validation
                            $validator = Validator::make($data, [
                                'date' => 'required|date',
                                'start_time' => 'required|date_format:H:i',
                                'end_time' => 'required|date_format:H:i|after:start_time',
                                'duration' => 'required|integer|min:30',
                                'max_reservations' => 'required|integer|min:1'
                            ]);

                            if ($validator->fails()) {
                                Notification::make()
                                    ->title('Erreur de validation')
                                    ->body($validator->errors()->first())
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Conversion des heures
                            $start = Carbon::parse($data['start_time']);
                            $end = Carbon::parse($data['end_time']);
                            $current = $start->copy();

                            // Vérification des créneaux existants
                            $existingSlots = Slot::where('date', $data['date'])
                                ->where(function ($query) use ($start, $end) {
                                    $query->whereBetween('start_time', [$start, $end])
                                        ->orWhereBetween('end_time', [$start, $end]);
                                })
                                ->exists();

                            if ($existingSlots) {
                                Notification::make()
                                    ->title('Conflit de créneaux')
                                    ->body('Des créneaux existent déjà dans cette plage horaire')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Génération des créneaux
                            $createdSlots = 0;
                            while ($current < $end) {
                                $slotEnd = $current->copy()->addMinutes($data['duration']);

                                if ($slotEnd > $end) break;

                                Slot::create([
                                    'date' => $data['date'],
                                    'start_time' => $current->format('H:i'),
                                    'end_time' => $slotEnd->format('H:i'),
                                    'max_reservations' => $data['max_reservations'],
                                    'available' => true
                                ]);

                                $createdSlots++;
                                $current = $slotEnd;
                            }

                            Notification::make()
                                ->title('Génération terminée')
                                ->body("{$createdSlots} créneaux générés avec succès")
                                ->success()
                                ->send();
                        })
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d/m/Y')
                    ->label('Date')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->label('Début'),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->label('Fin'),    
                    
                Tables\Columns\IconColumn::make('available')
                    ->label('Disponible')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('block_reason')
                    ->label('Raison du blocage')
                    ->placeholder('---')
                    ->visible(fn ($record) => $record && !$record->available),
                    
                Tables\Columns\TextColumn::make('max_reservations')
                    ->label('Capacité max.'),
                    
                Tables\Columns\TextColumn::make('reservations_count')
                    ->label('Résas')
                    ->counts('reservations'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Jusqu\'à'),
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
                Tables\Filters\Filter::make('available')
                    ->label('Disponibilité')
                    ->form([
                        Forms\Components\Select::make('availability')
                            ->label('Disponibilité')
                            ->options([
                                true => 'Disponibles',
                                false => 'Bloqués',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['availability']),
                            fn (Builder $query): Builder => $query->where('available', $data['availability']),
                        );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('warning'), // Couleur jaune/orange pour l'édition
                    
                Tables\Actions\DeleteAction::make()
                    ->color('danger'), // Couleur rouge pour la suppression
                    
                Tables\Actions\Action::make('toggleAvailability')
                    ->label(fn ($record): string => $record && $record->available ? 'Bloquer' : 'Débloquer')
                    ->icon(fn ($record): string => $record && $record->available ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn ($record): string => $record && $record->available ? 'primary' : 'success') // Bleu pour bloquer, vert pour débloquer
                    ->form(fn ($record) => [
                        Forms\Components\Textarea::make('block_reason')
                            ->label('Raison du blocage')
                            ->placeholder('Indiquez la raison pour laquelle ce créneau est bloqué')
                            ->required(fn () => $record && $record->available)
                            ->hidden(fn () => $record && !$record->available),
                    ])
                    ->action(function (Slot $record, array $data): void {
                        if ($record->available) {
                            // Si le créneau est actuellement disponible, on le bloque
                            $record->update([
                                'available' => false,
                                'block_reason' => $data['block_reason'] ?? null,
                            ]);
                            
                            Notification::make()
                                ->title('Créneau bloqué')
                                ->success()
                                ->send();
                        } else {
                            // Si le créneau est actuellement bloqué, on le débloque
                            $record->update([
                                'available' => true,
                                'block_reason' => null,
                            ]);
                            
                            Notification::make()
                                ->title('Créneau débloqué')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulkBlock')
                    ->label('Bloquer les créneaux sélectionnés')
                    ->icon('heroicon-o-lock-closed')
                    ->color('primary') // Bleu pour bloquer en masse
                    ->form([
                        Forms\Components\Textarea::make('bulk_block_reason')
                            ->label('Raison du blocage')
                            ->placeholder('Indiquez la raison pour laquelle ces créneaux sont bloqués')
                            ->required(),
                    ])
                    ->action(function (array $records, array $data): void {
                        foreach ($records as $record) {
                            $record->update([
                                'available' => false,
                                'block_reason' => $data['bulk_block_reason']
                            ]);
                        }
                        
                        Notification::make()
                            ->title(count($records) . ' créneaux ont été bloqués')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\BulkAction::make('bulkUnblock')
                    ->label('Débloquer les créneaux sélectionnés')
                    ->icon('heroicon-o-lock-open')
                    ->color('success') // Vert pour débloquer en masse
                    ->action(function (array $records): void {
                        foreach ($records as $record) {
                            $record->update([
                                'available' => true,
                                'block_reason' => null
                            ]);
                        }
                        
                        Notification::make()
                            ->title(count($records) . ' créneaux ont été débloqués')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteBulkAction::make()
                    ->color('danger'), // Rouge pour la suppression en masse
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSlots::route('/'),
            'create' => Pages\CreateSlot::route('/create'),
            'edit' => Pages\EditSlot::route('/{record}/edit'),
        ];
    }
}