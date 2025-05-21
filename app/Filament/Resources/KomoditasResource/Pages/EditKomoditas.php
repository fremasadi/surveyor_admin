<?php

namespace App\Filament\Resources\KomoditasResource\Pages;

use App\Filament\Resources\KomoditasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKomoditas extends EditRecord
{
    protected static string $resource = KomoditasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
