<?php

namespace App\Filament\Resources\KetuaLingkunganResource\Pages;

use App\Filament\Resources\KetuaLingkunganResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKetuaLingkungan extends CreateRecord
{
    protected static string $resource = KetuaLingkunganResource::class;

    protected function getRedirectUrl(): string
    {
        return KetuaLingkunganResource::getUrl('index');
    }
}
