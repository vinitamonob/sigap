<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganLainResource;

class CreateKeteranganLain extends CreateRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getRedirectUrl(): string
    {
        return KeteranganLainResource::getUrl('index');
    }
}
