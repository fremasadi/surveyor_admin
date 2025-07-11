<?php

namespace App\Filament\Resources\PasarResource\Pages;

use App\Filament\Resources\PasarResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePasar extends CreateRecord
{
    protected static string $resource = PasarResource::class;

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
