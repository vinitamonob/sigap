<?php

namespace App\Filament\Resources\ArsipSuratResource\Pages;

use App\Filament\Resources\ArsipSuratResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArsipSurat extends EditRecord
{
    protected static string $resource = ArsipSuratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
