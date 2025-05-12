<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Models\User;
use App\Models\Surat;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganLainResource;
use App\Models\DetailUser;

class CreateKeteranganLain extends CreateRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Jika user_id tidak dipilih (input manual)
        if (empty($data['user_id'])) {
            // Buat user baru
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['akun_email'],
                'password' => bcrypt('12345678'), // Password default
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'telepon' => $data['telepon'],
            ]);

            // Buat detail user
            DetailUser::create([
                'user_id' => $user->id,
                'lingkungan_id' => $data['lingkungan_id'],
                'alamat' => $data['alamat'],
            ]);

            $data['user_id'] = $user->id;
        }

        // Buat record keterangan lain
        $record = static::getModel()::create($data);
        
        // Buat surat terkait
        $surat = Surat::create([
            'user_id' => $data['user_id'],
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'keterangan_lain',
            'perihal' => 'Keterangan Lain',
            'tgl_surat' => $data['tgl_surat'],
            'status' => 'menunggu',
        ]);
        
        $record->update(['surat_id' => $surat->id]);
        
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}