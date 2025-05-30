<?php

namespace App\Filament\Resources\PendaftaranPerkawinanResource\Pages;

use App\Filament\Resources\PendaftaranPerkawinanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranPerkawinans extends ListRecords
{
    protected static string $resource = PendaftaranPerkawinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
