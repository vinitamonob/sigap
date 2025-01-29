<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use App\Filament\Resources\KeteranganKematianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeteranganKematians extends ListRecords
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
