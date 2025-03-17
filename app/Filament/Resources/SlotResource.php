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


// use App\Filament\Resources\SlotResource\Pages\ListSlots;
// use App\Filament\Resources\SlotResource\Pages\CreateSlot;
// use App\Filament\Resources\SlotResource\Pages\EditSlot;


class SlotResource extends Resource
{
    protected static ?string $model = Slot::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Créneaux Horaires';
    protected static ?string $modelLabel = 'Créneau';

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

                // Bouton de génération
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('generateSlots')
                        ->label('Générer les créneaux')
                        ->icon('heroicon-m-plus')
                        ->action(function ($livewire) {
                            $data = $livewire->form->getState();
                            
                            // Validation
                            $validator = Validator::make($data, [
                                // 'association_id' => 'required|exists:users,id',
                                'date' => 'required|date',
                                'start_time' => 'required|date_format:H:i',
                                'end_time' => 'required|date_format:H:i|after:start_time',
                                'duration' => 'required|integer|min:30'
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
                                // ->where('association_id', $data['association_id'])
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
                                    // 'association_id' => $data['association_id'],
                                    'date' => $data['date'],
                                    'start_time' => $current->format('H:i'),
                                    'end_time' => $slotEnd->format('H:i'),
                                    'max_reservations' => 50,
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
                    ->label('Dispo')
                    ->boolean(),
                    
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
                            ->label('Jusquà'),
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
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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