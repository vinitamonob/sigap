<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranBaptisResource;

class CreatePendaftaranBaptis extends CreateRecord
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $image = $data['tanda_tangan_ortu'];  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));

        $data['tanda_tangan_ortu'] = $imageName;
        // dd($data);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PendaftaranBaptisResource::getUrl('index');
    }
}
