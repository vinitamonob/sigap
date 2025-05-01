<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Services\SuratBaptisGenerate;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranBaptisResource;

class CreatePendaftaranBaptis extends CreateRecord
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $create = static::getModel()::create($data);

        $templatePath = 'templates/surat_pendaftaran_baptis.docx';
        $namaSurat = $create->nama_lingkungan .'-'.$create->tanggal_daftar.'-';
        $outputPath = storage_path('app/public/'.$namaSurat.'surat_pendaftaran_baptis.docx');
        $generateSurat = (new SuratBaptisGenerate)->generateFromTemplate(
            $templatePath,  
            $outputPath,
            $create->toArray(),
            'ortu',
            'ketua',
            'paroki'
        );
        return $create;
    }   
    
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
