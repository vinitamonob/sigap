<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganKematianResource;

class CreateKeteranganKematian extends CreateRecord
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function getRedirectUrl(): string
    {
        return KeteranganKematianResource::getUrl('index');
    }
}
