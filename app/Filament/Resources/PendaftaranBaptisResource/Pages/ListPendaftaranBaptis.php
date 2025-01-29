<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use App\Filament\Resources\PendaftaranBaptisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranBaptis extends ListRecords
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
