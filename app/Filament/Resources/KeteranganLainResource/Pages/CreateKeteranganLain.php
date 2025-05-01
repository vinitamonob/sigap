<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use App\Services\SuratLainGenerate;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganLainResource;

class CreateKeteranganLain extends CreateRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $create = static::getModel()::create($data);

        $templatePath = 'templates/surat_keterangan_lain.docx';
        $namaSurat = $create->nama_lingkungan .'-'.$create->tanggal_surat.'-';
        $outputPath = storage_path('app/public/'.$namaSurat.'surat_keteragan_lain.docx');
        $generateSurat = (new SuratLainGenerate)->generateFromTemplate(
            $templatePath,  
            $outputPath,
            $create->toArray(),
            'ketua',
            'paroki'
        );
        return $create;
    }   
    
    protected function getRedirectUrl(): string
    {
        return KeteranganLainResource::getUrl('index');
    }
}
