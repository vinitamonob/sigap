<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganKematianResource;
use App\Models\KeteranganKematian;
use App\Services\SuratKematianGenerate;
use Illuminate\Database\Eloquent\Model;

class CreateKeteranganKematian extends CreateRecord
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $create = static::getModel()::create($data);

        $templatePath = 'templates/surat_keterangan_kematian.docx';
        $namaSurat = $create->nama_lingkungan .'-'.$create->tanggal_surat.'-';
        $outputPath = storage_path('app/public/'.$namaSurat.'surat_keteragan_kematian.docx');
        $generateSurat = (new SuratKematianGenerate)->generateFromTemplate(
            $templatePath,  
            $outputPath,
            $create->toArray(),
            'ketua'
        );
        return $create;
    }   

    protected function getRedirectUrl(): string
    {
        return KeteranganKematianResource::getUrl('index');
    }

}
