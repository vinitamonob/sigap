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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $image = $data['tanda_tangan_ketua'];  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));

        $data['tanda_tangan_ketua'] = $imageName;
        // dd($data);
        return $data;
    }
}
