<?php

namespace App\Filament\Resources\PendaftaranBaptisResource\Pages;

use App\Filament\Resources\PendaftaranBaptisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Lingkungan;
use App\Models\KetuaLingkungan;
use App\Models\User;
use App\Models\DetailUser;
use App\Models\Keluarga;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class EditPendaftaranBaptis extends EditRecord
{
    protected static string $resource = PendaftaranBaptisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PendaftaranBaptisResource::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Mengisi data lingkungan dan paroki jika ada
        if (!empty($data['lingkungan_id'])) {
            $lingkungan = Lingkungan::find($data['lingkungan_id']);
            if ($lingkungan) {
                $data['nama_lingkungan'] = $lingkungan->nama_lingkungan;
                $data['paroki'] = $lingkungan->paroki ?? 'St. Stephanus Cilacap';
            }

            // Cari ketua lingkungan yang aktif
            $ketuaLingkungan = KetuaLingkungan::where('lingkungan_id', $data['lingkungan_id'])
                ->where('aktif', true)
                ->first();
            
            if ($ketuaLingkungan) {
                $data['ketua_lingkungan_id'] = $ketuaLingkungan->id;
            }
        }

        // Jika ada user_id, isi data user
        if (!empty($data['user_id'])) {
            $user = User::with(['detailUser', 'detailUser.keluarga'])->find($data['user_id']);
            
            if ($user) {
                $data['nama_lengkap'] = $user->name;
                $data['akun_email'] = $user->email;
                $data['jenis_kelamin'] = $user->jenis_kelamin;
                $data['tempat_lahir'] = $user->tempat_lahir;
                $data['tgl_lahir'] = $user->tgl_lahir;
                $data['telepon'] = $user->telepon;
                
                // Data dari detail user
                if ($user->detailUser) {
                    $data['nama_baptis'] = $user->detailUser->nama_baptis;
                    $data['alamat'] = $user->detailUser->alamat;
                    
                    // Data dari keluarga
                    if ($user->detailUser->keluarga) {
                        $data['nama_ayah'] = $user->detailUser->keluarga->nama_ayah;
                        $data['agama_ayah'] = $user->detailUser->keluarga->agama_ayah;
                        $data['nama_ibu'] = $user->detailUser->keluarga->nama_ibu;
                        $data['agama_ibu'] = $user->detailUser->keluarga->agama_ibu;
                        $data['alamat_keluarga'] = $user->detailUser->keluarga->alamat_ayah;
                        $data['ttd_ortu'] = $user->detailUser->keluarga->ttd_ayah;
                    }
                }
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Handle signature upload jika ada perubahan
        if (isset($this->data['ttd_ortu']) && Str::startsWith($this->data['ttd_ortu'], 'data:image')) {
            $this->data['ttd_ortu'] = $this->saveSignature($this->data['ttd_ortu']);
        }

        // Update user data jika ada perubahan
        if (!empty($this->data['user_id'])) {
            $user = User::find($this->data['user_id']);
            
            if ($user) {
                $userUpdates = [];
                if ($user->name !== $this->data['nama_lengkap']) {
                    $userUpdates['name'] = $this->data['nama_lengkap'];
                }
                if ($user->email !== $this->data['akun_email']) {
                    $userUpdates['email'] = $this->data['akun_email'];
                }
                if ($user->jenis_kelamin !== $this->data['jenis_kelamin']) {
                    $userUpdates['jenis_kelamin'] = $this->data['jenis_kelamin'];
                }
                if ($user->tempat_lahir !== $this->data['tempat_lahir']) {
                    $userUpdates['tempat_lahir'] = $this->data['tempat_lahir'];
                }
                if ($user->tgl_lahir != $this->data['tgl_lahir']) {
                    $userUpdates['tgl_lahir'] = $this->data['tgl_lahir'];
                }
                if ($user->telepon !== $this->data['telepon']) {
                    $userUpdates['telepon'] = $this->data['telepon'];
                }
                
                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }
                
                // Update detail user
                $detailUser = DetailUser::firstOrNew(['user_id' => $user->id]);
                $detailUser->lingkungan_id = $this->data['lingkungan_id'];
                $detailUser->nama_baptis = $this->data['nama_baptis'];
                $detailUser->alamat = $this->data['alamat'];
                $detailUser->save();
                
                // Update keluarga
                if ($detailUser->keluarga) {
                    $keluargaUpdates = [
                        'nama_ayah' => $this->data['nama_ayah'],
                        'agama_ayah' => $this->data['agama_ayah'],
                        'nama_ibu' => $this->data['nama_ibu'],
                        'agama_ibu' => $this->data['agama_ibu'],
                        'alamat_ayah' => $this->data['alamat_keluarga'],
                        'alamat_ibu' => $this->data['alamat_keluarga'],
                    ];
                    
                    // Update tanda tangan hanya jika ada yang baru
                    if (isset($this->data['ttd_ortu']) && $this->data['ttd_ortu'] !== $detailUser->keluarga->ttd_ayah) {
                        $keluargaUpdates['ttd_ayah'] = $this->data['ttd_ortu'];
                    }
                    
                    $detailUser->keluarga->update($keluargaUpdates);
                } else {
                    $keluarga = Keluarga::create([
                        'nama_ayah' => $this->data['nama_ayah'],
                        'agama_ayah' => $this->data['agama_ayah'],
                        'nama_ibu' => $this->data['nama_ibu'],
                        'agama_ibu' => $this->data['agama_ibu'],
                        'alamat_ayah' => $this->data['alamat_keluarga'],
                        'alamat_ibu' => $this->data['alamat_keluarga'],
                        'ttd_ayah' => $this->data['ttd_ortu'] ?? null,
                    ]);
                    
                    $detailUser->keluarga_id = $keluarga->id;
                    $detailUser->save();
                }
            }
        }
    }

    protected function saveSignature($signature): string
    {
        $image = str_replace('data:image/png;base64,', '', $signature);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.png';
        File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
        return 'storage/signatures/' . $imageName;
    }
}