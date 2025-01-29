<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeteranganLains extends ListRecords
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
