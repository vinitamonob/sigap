<?php

namespace App\Filament\Resources\KetuaLingkunganResource\Pages;

use App\Filament\Resources\KetuaLingkunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKetuaLingkungans extends ListRecords
{
    protected static string $resource = KetuaLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
