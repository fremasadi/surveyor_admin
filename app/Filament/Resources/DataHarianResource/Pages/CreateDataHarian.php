<?php

namespace App\Filament\Resources\DataHarianResource\Pages;

use App\Filament\Resources\DataHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDataHarian extends CreateRecord
{
    protected static string $resource = DataHarianResource::class;

    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
