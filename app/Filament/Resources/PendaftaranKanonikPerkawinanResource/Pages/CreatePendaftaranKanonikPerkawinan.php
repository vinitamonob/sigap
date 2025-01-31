<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource;

class CreatePendaftaranKanonikPerkawinan extends CreateRecord
{
    protected static string $resource = PendaftaranKanonikPerkawinanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Array tanda tangan yang perlu diproses
        $tandaTanganFields = [
            'tanda_tangan_ketua_istri',
            'tanda_tangan_ketua_suami',
            'tanda_tangan_calon_istri',
            'tanda_tangan_calon_suami'
        ];

        foreach ($tandaTanganFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $image = $data[$field];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.png';
                File::put(storage_path() . '/' . $imageName, base64_decode($image));
                
                $data[$field] = $imageName;
            }
        }
        // dd($data);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return PendaftaranKanonikPerkawinanResource::getUrl('index');
    }
}
