<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Quota;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;

class QuotaTable extends BaseWidget
{
    protected function getTableQuery(): Builder
    {
        return Quota::query()->with('association');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('association.name')
                ->label('Association')
                ->sortable(),
            TextColumn::make('quantite')
                ->label('Quantité')
                ->sortable(),
            TextColumn::make('grand')
                ->label('Grand (%)')
                ->formatStateUsing(fn ($state) => $state . '%'),
            TextColumn::make('moyen')
                ->label('Moyen (%)')
                ->formatStateUsing(fn ($state) => $state . '%'),
            TextColumn::make('petit')
                ->label('Petit (%)')
                ->formatStateUsing(fn ($state) => $state . '%'),
        ];
    }
}