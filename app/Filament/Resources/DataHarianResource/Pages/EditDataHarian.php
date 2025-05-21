<?php

namespace App\Filament\Resources\DataHarianResource\Pages;

use App\Filament\Resources\DataHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataHarian extends EditRecord
{
    protected static string $resource = DataHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
