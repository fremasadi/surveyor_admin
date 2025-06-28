<?php

namespace App\Filament\Resources\PasarResource\Pages;

use App\Filament\Resources\PasarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPasar extends EditRecord
{
    protected static string $resource = PasarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
