<?php

namespace App\Filament\Resources\QuotaResource\Pages;

use App\Filament\Resources\QuotaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuota extends EditRecord
{
    protected static string $resource = QuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
