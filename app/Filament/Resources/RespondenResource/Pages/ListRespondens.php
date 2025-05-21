<?php

namespace App\Filament\Resources\RespondenResource\Pages;

use App\Filament\Resources\RespondenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRespondens extends ListRecords
{
    protected static string $resource = RespondenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
