<?php

namespace App\Filament\Resources\KeteranganKematianResource\Pages;

use App\Filament\Resources\KeteranganKematianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Lingkungan;
use App\Models\KetuaLingkungan;
use App\Models\User;
use Carbon\Carbon;

class EditKeteranganKematian extends EditRecord
{
    protected static string $resource = KeteranganKematianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return KeteranganKematianResource::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Mengisi data lingkungan dan paroki jika ada
        if (!empty($data['lingkungan_id'])) {
            $lingkungan = Lingkungan::find($data['lingkungan_id']);
            if ($lingkungan) {
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
                
                // Hitung usia jika ada tanggal lahir
                if ($user->tgl_lahir) {
                    $data['usia'] = Carbon::parse($user->tgl_lahir)->age;
                }
                
                // Data dari detail user
                if ($user->detailUser) {
                    $data['tempat_baptis'] = $user->detailUser->tempat_baptis ?? '';
                    $data['no_baptis'] = $user->detailUser->no_baptis ?? '';
                    $data['lingkungan_id'] = $user->detailUser->lingkungan_id;
                    
                    // Data dari keluarga
                    if ($user->detailUser->keluarga) {
                        $data['nama_ortu'] = $user->detailUser->keluarga->nama_ayah . ' / ' . $user->detailUser->keluarga->nama_ibu;
                    }
                }
            }
        }

        return $data;
    }
}