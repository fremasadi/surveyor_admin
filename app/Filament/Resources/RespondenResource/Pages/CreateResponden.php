<?php

namespace App\Filament\Resources\RespondenResource\Pages;

use App\Filament\Resources\RespondenResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateResponden extends CreateRecord
{
    protected static string $resource = RespondenResource::class;

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

    public static function mutateFormDataBeforeUpdate(array $data): array
{
    if (isset($data['pasar_id'])) {
        $pasar = \App\Models\Pasar::find($data['pasar_id']);
        $data['address'] = $pasar?->lokasi ?? '';
    }

    return $data;
}

}
