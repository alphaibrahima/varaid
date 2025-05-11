<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;

class OwnerInfoFilter extends BaseFilter
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Recherche propriétaire')
            ->form([
                TextInput::make('search')
                    ->label('Rechercher')
                    ->placeholder('Nom, prénom, email, téléphone...')
                    ->helperText('Recherche dans les informations des propriétaires'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                if (empty($data['search'])) {
                    return $query;
                }

                $search = $data['search'];
                
                // Pour PostgreSQL
                if (config('database.default') === 'pgsql') {
                    $query->where(function ($query) use ($search) {
                        $query->whereRaw("owners_data::text ILIKE ?", ["%{$search}%"]);
                    });
                } 
                // Pour MySQL
                else {
                    $query->where(function ($query) use ($search) {
                        $query->whereRaw("JSON_SEARCH(LOWER(owners_data), 'one', LOWER(?)) IS NOT NULL", ["%{$search}%"]);
                    });
                }

                return $query;
            });
    }
}