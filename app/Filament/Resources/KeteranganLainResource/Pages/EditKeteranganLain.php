<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Lingkungan;
use App\Models\KetuaLingkungan;
use App\Models\User;
use App\Models\DetailUser;

class EditKeteranganLain extends EditRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return KeteranganLainResource::getUrl('index');
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
            $user = User::with('detailUser')->find($data['user_id']);
            
            if ($user) {
                $data['nama_lengkap'] = $user->name;
                $data['akun_email'] = $user->email;
                $data['tempat_lahir'] = $user->tempat_lahir;
                $data['tgl_lahir'] = $user->tgl_lahir;
                $data['telepon'] = $user->telepon;
                
                // Data dari detail user
                if ($user->detailUser) {
                    $data['alamat'] = $user->detailUser->alamat;
                    // Jika lingkungan di detail user berbeda, update
                    if ($user->detailUser->lingkungan_id != $data['lingkungan_id']) {
                        $data['lingkungan_id'] = $user->detailUser->lingkungan_id;
                    }
                }
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
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
                $detailUser->alamat = $this->data['alamat'];
                $detailUser->save();
            }
        }
    }
}