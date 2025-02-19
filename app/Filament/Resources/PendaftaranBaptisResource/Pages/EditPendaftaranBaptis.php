<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use App\Filament\Resources\PendaftaranBaptisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranBaptis extends EditRecord
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PendaftaranBaptisResource::getUrl('index');
    }
}
