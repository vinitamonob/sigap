<?php

namespace App\Filament\Resources\PendaftaranPerkawinanResource\Pages;

use App\Filament\Resources\PendaftaranPerkawinanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranPerkawinan extends EditRecord
{
    protected static string $resource = PendaftaranPerkawinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
