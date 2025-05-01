<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Services\SuratKanonikGenerate;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource;

class CreatePendaftaranKanonikPerkawinan extends CreateRecord
{
    protected static string $resource = PendaftaranKanonikPerkawinanResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $create = static::getModel()::create($data);

        $templatePath = 'templates/surat_pendaftaran_kanonik.docx';
        $namaSurat = $create->nama_lingkungan .'-'.$create->tanggal_daftar.'-';
        $outputPath = storage_path('app/public/'.$namaSurat.'surat_pendaftaran_kanonik.docx');
        $generateSurat = (new SuratKanonikGenerate)->generateFromTemplate(
            $templatePath,  
            $outputPath,
            $create->toArray(),
            'calon_istri',
            'ketua_istri',
            'calon_suami',
            'ketua_suami',
            'paroki'
        );
        return $create;
    }   

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
