<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use App\Filament\Resources\PendaftaranKanonikPerkawinanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranKanonikPerkawinans extends ListRecords
{
    protected static string $resource = PendaftaranKanonikPerkawinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
