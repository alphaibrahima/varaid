<?php

namespace App\Filament\Resources\AcheteurResource\Pages;

use App\Filament\Resources\AcheteurResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcheteur extends EditRecord
{
    protected static string $resource = AcheteurResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['phone'] = $data['country_code'] . preg_replace('/\D/', '', $data['local_phone']);
        unset($data['country_code'], $data['local_phone']);
        return $data;
    }
}