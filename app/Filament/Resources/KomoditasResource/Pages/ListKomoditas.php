<?php

namespace App\Filament\Resources\KomoditasResource\Pages;

use App\Filament\Resources\KomoditasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKomoditas extends ListRecords
{
    protected static string $resource = KomoditasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
