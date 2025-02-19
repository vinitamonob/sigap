<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeteranganLain extends EditRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return KeteranganLainResource::getUrl('index');
    }
}
