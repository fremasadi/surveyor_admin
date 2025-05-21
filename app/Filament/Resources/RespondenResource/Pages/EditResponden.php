<?php

namespace App\Filament\Resources\RespondenResource\Pages;

use App\Filament\Resources\RespondenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResponden extends EditRecord
{
    protected static string $resource = RespondenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
