<?php

namespace App\Filament\Resources\LingkunganResource\Pages;

use App\Filament\Resources\LingkunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLingkungans extends ManageRecords
{
    protected static string $resource = LingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
