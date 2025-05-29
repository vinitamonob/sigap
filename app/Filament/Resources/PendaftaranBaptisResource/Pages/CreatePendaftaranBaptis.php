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
        // Handle signature upload
        if (isset($data['ttd_ortu'])) {
            $data['ttd_ortu'] = $this->saveSignature($data['ttd_ortu']);
        }

        // If user is selected from database
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            
            if ($user) {
                // Update user data if different from form
                $userUpdates = [];
                if ($user->name !== $data['nama_lengkap']) {
                    $userUpdates['name'] = $data['nama_lengkap'];
                }
                if ($user->email !== $data['akun_email']) {
                    $userUpdates['email'] = $data['akun_email'];
                }
                if ($user->jenis_kelamin !== $data['jenis_kelamin']) {
                    $userUpdates['jenis_kelamin'] = $data['jenis_kelamin'];
                }
                if ($user->tempat_lahir !== $data['tempat_lahir']) {
                    $userUpdates['tempat_lahir'] = $data['tempat_lahir'];
                }
                if ($user->tgl_lahir != $data['tgl_lahir']) {
                    $userUpdates['tgl_lahir'] = $data['tgl_lahir'];
                }
                if ($user->telepon !== $data['telepon']) {
                    $userUpdates['telepon'] = $data['telepon'];
                }
                
                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }
                
                // Update or create detail user
                $detailUser = DetailUser::firstOrNew(['user_id' => $user->id]);
                $detailUser->lingkungan_id = $data['lingkungan_id'];
                $detailUser->nama_baptis = $data['nama_baptis'];
                $detailUser->alamat = $data['alamat'];
                $detailUser->save();
                
                // Update or create keluarga
                if ($detailUser->keluarga) {
                    $keluargaUpdates = [];
                    if ($detailUser->keluarga->nama_ayah !== $data['nama_ayah']) {
                        $keluargaUpdates['nama_ayah'] = $data['nama_ayah'];
                    }
                    if ($detailUser->keluarga->agama_ayah !== $data['agama_ayah']) {
                        $keluargaUpdates['agama_ayah'] = $data['agama_ayah'];
                    }
                    if ($detailUser->keluarga->nama_ibu !== $data['nama_ibu']) {
                        $keluargaUpdates['nama_ibu'] = $data['nama_ibu'];
                    }
                    if ($detailUser->keluarga->agama_ibu !== $data['agama_ibu']) {
                        $keluargaUpdates['agama_ibu'] = $data['agama_ibu'];
                    }
                    if ($detailUser->keluarga->alamat_ayah !== $data['alamat_keluarga']) {
                        $keluargaUpdates['alamat_ayah'] = $data['alamat_keluarga'];
                        $keluargaUpdates['alamat_ibu'] = $data['alamat_keluarga'];
                    }
                    if (isset($data['ttd_ortu']) && empty($detailUser->keluarga->ttd_ayah)) {
                        $keluargaUpdates['ttd_ayah'] = $data['ttd_ortu'];
                    }
                    
                    if (!empty($keluargaUpdates)) {
                        $detailUser->keluarga->update($keluargaUpdates);
                    }
                } else {
                    $keluarga = Keluarga::create([
                        'nama_ayah' => $data['nama_ayah'],
                        'agama_ayah' => $data['agama_ayah'],
                        'nama_ibu' => $data['nama_ibu'],
                        'agama_ibu' => $data['agama_ibu'],
                        'alamat_ayah' => $data['alamat_keluarga'],
                        'alamat_ibu' => $data['alamat_keluarga'],
                        'ttd_ayah' => $data['ttd_ortu'] ?? null,
                    ]);
                    
                    $detailUser->keluarga_id = $keluarga->id;
                    $detailUser->save();
                }
            }
        } 
        // If user is not selected (manual input)
        else {
            // Create new user
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['akun_email'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'telepon' => $data['telepon'],
            ]);

            // Assign role 'umat'
            $user->assignRole('umat');

            // Create keluarga
            $keluarga = Keluarga::create([
                'nama_ayah' => $data['nama_ayah'],
                'agama_ayah' => $data['agama_ayah'],
                'nama_ibu' => $data['nama_ibu'],
                'agama_ibu' => $data['agama_ibu'],
                'alamat_ayah' => $data['alamat_keluarga'],
                'alamat_ibu' => $data['alamat_keluarga'],
                'ttd_ayah' => $data['ttd_ortu'] ?? null,
            ]);

            // Create detail user
            DetailUser::create([
                'user_id' => $user->id,
                'lingkungan_id' => $data['lingkungan_id'],
                'keluarga_id' => $keluarga->id,
                'nama_baptis' => $data['nama_baptis'],
                'alamat' => $data['alamat'],
            ]);

            $data['user_id'] = $user->id;
        }

        // Create the PendaftaranBaptis record
        $record = static::getModel()::create($data);
        
        // Create related Surat record
        $surat = Surat::create([
            'user_id' => $data['user_id'],
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'pendaftaran_baptis',
            'perihal' => 'Pendaftaran Baptis',
            'tgl_surat' => $data['tgl_surat'],
            'status' => 'menunggu',
        ]);
        
        // Link the surat to pendaftaran baptis record
        $record->update(['surat_id' => $surat->id]);
        
        return $record;
    }

    protected function saveSignature($signature): string
    {
        $image = str_replace('data:image/png;base64,', '', $signature);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.png';
        File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
        return 'storage/signatures/' . $imageName;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}