<?php

namespace App\Filament\Resources\KetuaLingkunganResource\Pages;

use App\Filament\Resources\KetuaLingkunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKetuaLingkungan extends EditRecord
{
    protected static string $resource = KetuaLingkunganResource::class;

    protected function getRedirectUrl(): string
    {
        return KetuaLingkunganResource::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
