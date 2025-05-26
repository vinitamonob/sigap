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
            File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
            $data['ttd_ortu'] = 'storage/signatures/' . $imageName;
        }

        // Jika user_id dipilih (pilih umat yang sudah ada)
        if (!empty($data['user_id'])) {
            $user = User::with(['detailUser', 'detailUser.keluarga'])->find($data['user_id']);
            
            if ($user) {
                // Update data user jika ada field yang null
                $userUpdates = [];
                if (empty($user->jenis_kelamin) && !empty($data['jenis_kelamin'])) {
                    $userUpdates['jenis_kelamin'] = $data['jenis_kelamin'];
                }
                if (empty($user->tempat_lahir) && !empty($data['tempat_lahir'])) {
                    $userUpdates['tempat_lahir'] = $data['tempat_lahir'];
                }
                if (empty($user->tgl_lahir) && !empty($data['tgl_lahir'])) {
                    $userUpdates['tgl_lahir'] = $data['tgl_lahir'];
                }
                if (empty($user->telepon) && !empty($data['telepon'])) {
                    $userUpdates['telepon'] = $data['telepon'];
                }
                
                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }
                
                // Update detail user jika ada
                if ($user->detailUser) {
                    $detailUpdates = [];
                    if (empty($user->detailUser->nama_baptis) && !empty($data['nama_baptis'])) {
                        $detailUpdates['nama_baptis'] = $data['nama_baptis'];
                    }
                    if (empty($user->detailUser->alamat) && !empty($data['alamat'])) {
                        $detailUpdates['alamat'] = $data['alamat'];
                    }
                    if (empty($user->detailUser->lingkungan_id) && !empty($data['lingkungan_id'])) {
                        $detailUpdates['lingkungan_id'] = $data['lingkungan_id'];
                    }
                    
                    if (!empty($detailUpdates)) {
                        $user->detailUser->update($detailUpdates);
                    }
                    
                    // Update keluarga jika ada
                    if ($user->detailUser->keluarga) {
                        $keluargaUpdates = [];
                        if (empty($user->detailUser->keluarga->nama_ayah) && !empty($data['nama_ayah'])) {
                            $keluargaUpdates['nama_ayah'] = $data['nama_ayah'];
                        }
                        if (empty($user->detailUser->keluarga->agama_ayah) && !empty($data['agama_ayah'])) {
                            $keluargaUpdates['agama_ayah'] = $data['agama_ayah'];
                        }
                        if (empty($user->detailUser->keluarga->nama_ibu) && !empty($data['nama_ibu'])) {
                            $keluargaUpdates['nama_ibu'] = $data['nama_ibu'];
                        }
                        if (empty($user->detailUser->keluarga->agama_ibu) && !empty($data['agama_ibu'])) {
                            $keluargaUpdates['agama_ibu'] = $data['agama_ibu'];
                        }
                        if (empty($user->detailUser->keluarga->alamat_ayah) && !empty($data['alamat_keluarga'])) {
                            $keluargaUpdates['alamat_ayah'] = $data['alamat_keluarga'];
                            $keluargaUpdates['alamat_ibu'] = $data['alamat_keluarga'];
                        }
                        if (empty($user->detailUser->keluarga->ttd_ayah) && !empty($data['ttd_ortu'])) {
                            $keluargaUpdates['ttd_ayah'] = $data['ttd_ortu'];
                        }
                        
                        if (!empty($keluargaUpdates)) {
                            $user->detailUser->keluarga->update($keluargaUpdates);
                        }
                    } else {
                        // Buat keluarga baru jika tidak ada
                        $keluarga = Keluarga::create([
                            'nama_ayah' => $data['nama_ayah'],
                            'agama_ayah' => $data['agama_ayah'],
                            'nama_ibu' => $data['nama_ibu'],
                            'agama_ibu' => $data['agama_ibu'],
                            'alamat_ayah' => $data['alamat_keluarga'],
                            'alamat_ibu' => $data['alamat_keluarga'],
                            'ttd_ayah' => $data['ttd_ortu'],
                        ]);
                        
                        $user->detailUser->update(['keluarga_id' => $keluarga->id]);
                        $data['keluarga_id'] = $keluarga->id;
                    }
                } else {
                    // Buat detail user baru jika tidak ada
                    $keluarga = Keluarga::create([
                        'nama_ayah' => $data['nama_ayah'],
                        'agama_ayah' => $data['agama_ayah'],
                        'nama_ibu' => $data['nama_ibu'],
                        'agama_ibu' => $data['agama_ibu'],
                        'alamat_ayah' => $data['alamat_keluarga'],
                        'alamat_ibu' => $data['alamat_keluarga'],
                        'ttd_ayah' => $data['ttd_ortu'],
                    ]);
                    
                    $detailUser = DetailUser::create([
                        'user_id' => $user->id,
                        'lingkungan_id' => $data['lingkungan_id'],
                        'keluarga_id' => $keluarga->id,
                        'nama_baptis' => $data['nama_baptis'],
                        'alamat' => $data['alamat'],
                    ]);
                    
                    $data['keluarga_id'] = $keluarga->id;
                }
            }
        } else {
            // Jika user_id tidak dipilih (input manual)
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

            // Assign role 'umat'
            $user->assignRole('umat');

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