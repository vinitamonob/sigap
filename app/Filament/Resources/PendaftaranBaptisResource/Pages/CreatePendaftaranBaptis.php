<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use App\Models\User;
use App\Models\Surat;
use App\Models\Keluarga;
use App\Models\DetailUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranBaptisResource;

class CreatePendaftaranBaptis extends CreateRecord
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Simpan tanda tangan ortu
        if (isset($data['ttd_ortu'])) {
            $image = $data['ttd_ortu'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10).'.png';
            File::put(storage_path(). '/' . $imageName, base64_decode($image));
            $data['ttd_ortu'] = $imageName;
        }

        // Jika user_id tidak dipilih (input manual)
        if (empty($data['user_id'])) {
            // Buat user baru
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['akun_email'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'telepon' => $data['telepon'],
            ]);

            // Buat keluarga baru
            $keluarga = Keluarga::create([
                'nama_ayah' => $data['nama_ayah'],
                'agama_ayah' => $data['agama_ayah'],
                'nama_ibu' => $data['nama_ibu'],
                'agama_ibu' => $data['agama_ibu'],
                'alamat_ayah' => $data['alamat_keluarga'],
                'alamat_ibu' => $data['alamat_keluarga'],
                'ttd_ayah' => $data['ttd_ortu'],
            ]);

            // Buat detail user baru
            $detailUser = DetailUser::create([
                'user_id' => $user->id,
                'lingkungan_id' => $data['lingkungan_id'],
                'keluarga_id' => $keluarga->id,
                'nama_baptis' => $data['nama_baptis'],
                'alamat' => $data['alamat'],
            ]);

            $data['user_id'] = $user->id;
            $data['keluarga_id'] = $keluarga->id;
        }

        // Buat record pendaftaran baptis
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}