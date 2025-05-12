<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use App\Models\Surat;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganKematianResource;

class CreateKeteranganKematian extends CreateRecord
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);
        
        // Buat Surat terkait
        $surat = Surat::create([
            'user_id' => $data['user_id'] ?? null,
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'keterangan_kematian',
            'perihal' => 'Keterangan Kematian',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu'
        ]);
        
        // Update KeteranganKematian dengan ID surat
        $record->update(['surat_id' => $surat->id]);
        
        return $record;
    }   

    protected function getRedirectUrl(): string
    {
        return KeteranganKematianResource::getUrl('index');
    }
}