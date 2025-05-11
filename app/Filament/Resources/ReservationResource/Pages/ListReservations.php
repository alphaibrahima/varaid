<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\Layout;
use App\Models\Reservation;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\MaxWidth;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    // Définir la disposition des filtres en accordéon pour plus d'espace
    protected function getTableFiltersLayout(): ?string
    {
        return Layout::AboveContent;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle réservation'),
                
            // Ajouter un bouton d'export CSV/Excel si nécessaire
            Actions\Action::make('export')
                ->label('Exporter')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Logique d'export
                })
                ->requiresConfirmation()
                ->modalHeading('Exporter les réservations')
                ->modalDescription('Voulez-vous exporter toutes les réservations actuellement filtrées ?')
                ->modalSubmitActionLabel('Confirmer l\'export'),
        ];
    }
    
    // Ajouter un infolist pour afficher les détails dans un modal
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Détails de la réservation')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Code'),
                            
                        Infolists\Components\TextEntry::make('status')
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
                            
                        Infolists\Components\TextEntry::make('date')
                            ->label('Date')
                            ->date('d/m/Y'),
                            
                        Infolists\Components\TextEntry::make('slot.start_time')
                            ->label('Heure de début')
                            ->formatStateUsing(fn ($state, $record) => $record->slot ? substr($record->slot->start_time, 0, 5) : 'N/A'),
                            
                        Infolists\Components\TextEntry::make('slot.end_time')
                            ->label('Heure de fin')
                            ->formatStateUsing(fn ($state, $record) => $record->slot ? substr($record->slot->end_time, 0, 5) : 'N/A'),
                            
                        Infolists\Components\TextEntry::make('size')
                            ->label('Taille')
                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                            
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Quantité'),
                            
                        Infolists\Components\IconEntry::make('skip_selection')
                            ->label('Ne vient pas choisir')
                            ->boolean(),
                            
                        Infolists\Components\TextEntry::make('payment_intent_id')
                            ->label('ID de paiement')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créée le')
                            ->dateTime(),
                    ]),
                    
                Infolists\Components\Section::make('Informations acheteur')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Nom'),
                            
                        Infolists\Components\TextEntry::make('user.firstname')
                            ->label('Prénom'),
                            
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('Téléphone')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('user.full_address')
                            ->label('Adresse')
                            ->copyable(),
                            
                        Infolists\Components\TextEntry::make('association.name')
                            ->label('Association'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Propriétaires')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('owners_data')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\TextEntry::make('firstname')
                                    ->label('Prénom'),
                                    
                                Infolists\Components\TextEntry::make('lastname')
                                    ->label('Nom'),
                                    
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable(),
                                    
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Téléphone')
                                    ->copyable(),
                                    
                                Infolists\Components\TextEntry::make('address')
                                    ->label('Adresse')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->collapsed(),
            ])
            ->maxWidth(MaxWidth::SevenExtraLarge);
    }
}