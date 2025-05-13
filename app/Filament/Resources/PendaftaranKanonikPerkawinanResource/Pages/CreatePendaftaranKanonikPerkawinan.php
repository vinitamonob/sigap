<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use App\Models\Surat;
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
        // Buat record pendaftaran kanonik
        $record = static::getModel()::create($data);
        
        // Buat surat terkait
        $surat = Surat::create([
            'user_id' => $data['user_id'],
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'pendaftaran_baptis',
            'perihal' => 'Pendaftaran Baptis',
            'tgl_surat' => $data['tgl_surat'],
            'status' => 'menunggu',
        ]);
        
        $record->update(['surat_id' => $surat->id]);
        
        return $record;
    }   

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Array tanda tangan yang perlu diproses
        $tandaTanganFields = [
            'ttd_ketua_istri',
            'ttd_ketua_suami',
            'ttd_calon_istri',
            'ttd_calon_suami'
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
