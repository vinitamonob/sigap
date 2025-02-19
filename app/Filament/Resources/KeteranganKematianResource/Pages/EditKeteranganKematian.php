<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use App\Filament\Resources\KeteranganKematianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeteranganKematian extends EditRecord
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return KeteranganKematianResource::getUrl('index');
    }
}
