<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use App\Filament\Resources\PendaftaranKanonikPerkawinanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranKanonikPerkawinan extends EditRecord
{
    protected static string $resource = PendaftaranKanonikPerkawinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
