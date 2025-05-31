<?php

namespace App\Filament\Resources\PendaftaranPerkawinanResource\Pages;

use App\Filament\Resources\PendaftaranPerkawinanResource;
use App\Models\PendaftaranPerkawinan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Lingkungan;
use App\Models\KetuaLingkungan;
use App\Models\User;
use App\Models\DetailUser;
use App\Models\Keluarga;
use App\Models\CalonPasangan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class EditPendaftaranPerkawinan extends EditRecord
{
    protected static string $resource = PendaftaranPerkawinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PendaftaranPerkawinanResource::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ambil data calon istri dan suami
        $calonIstri = CalonPasangan::with(['user', 'keluarga', 'lingkungan'])->find($data['calon_istri_id']);
        $calonSuami = CalonPasangan::with(['user', 'keluarga', 'lingkungan'])->find($data['calon_suami_id']);

        if ($calonIstri) {
            // Data calon istri
            $data['nama_istri'] = $calonIstri->user->name;
            $data['akun_email_istri'] = $calonIstri->user->email;
            $data['tempat_lahir_istri'] = $calonIstri->user->tempat_lahir;
            $data['tgl_lahir_istri'] = $calonIstri->user->tgl_lahir;
            $data['telepon_istri'] = $calonIstri->user->telepon;
            $data['ttd_calon_istri'] = $calonIstri->user->tanda_tangan;

            // Data detail calon istri
            $data['tempat_baptis_istri'] = $calonIstri->user->detailUser->tempat_baptis ?? null;
            $data['tgl_baptis_istri'] = $calonIstri->user->detailUser->tgl_baptis ?? null;
            $data['alamat_sekarang_istri'] = $calonIstri->user->detailUser->alamat ?? null;
            $data['alamat_setelah_menikah_istri'] = $calonIstri->alamat_stlh_menikah;
            $data['pekerjaan_istri'] = $calonIstri->pekerjaan;
            $data['pendidikan_terakhir_istri'] = $calonIstri->pendidikan_terakhir;
            $data['agama_istri'] = $calonIstri->agama;

            // Data keluarga calon istri
            if ($calonIstri->keluarga) {
                $data['nama_ayah_istri'] = $calonIstri->keluarga->nama_ayah;
                $data['agama_ayah_istri'] = $calonIstri->keluarga->agama_ayah;
                $data['pekerjaan_ayah_istri'] = $calonIstri->keluarga->pekerjaan_ayah;
                $data['alamat_ayah_istri'] = $calonIstri->keluarga->alamat_ayah;
                $data['nama_ibu_istri'] = $calonIstri->keluarga->nama_ibu;
                $data['agama_ibu_istri'] = $calonIstri->keluarga->agama_ibu;
                $data['pekerjaan_ibu_istri'] = $calonIstri->keluarga->pekerjaan_ibu;
                $data['alamat_ibu_istri'] = $calonIstri->keluarga->alamat_ibu;
            }

            // Data lingkungan calon istri
            $data['lingkungan_istri_id'] = $calonIstri->lingkungan_id;
            $data['nama_lingkungan_istri'] = $calonIstri->nama_lingkungan;
            $data['wilayah_istri'] = $calonIstri->wilayah;
            $data['paroki_istri'] = $calonIstri->paroki;
            $data['nama_ketua_istri'] = $calonIstri->nama_ketua;
            $data['ttd_ketua_istri'] = $calonIstri->ttd_ketua;
        }

        if ($calonSuami) {
            // Data calon suami
            $data['nama_suami'] = $calonSuami->user->name;
            $data['akun_email_suami'] = $calonSuami->user->email;
            $data['tempat_lahir_suami'] = $calonSuami->user->tempat_lahir;
            $data['tgl_lahir_suami'] = $calonSuami->user->tgl_lahir;
            $data['telepon_suami'] = $calonSuami->user->telepon;
            $data['ttd_calon_suami'] = $calonSuami->user->tanda_tangan;

            // Data detail calon suami
            $data['tempat_baptis_suami'] = $calonSuami->user->detailUser->tempat_baptis ?? null;
            $data['tgl_baptis_suami'] = $calonSuami->user->detailUser->tgl_baptis ?? null;
            $data['alamat_sekarang_suami'] = $calonSuami->user->detailUser->alamat ?? null;
            $data['alamat_setelah_menikah_suami'] = $calonSuami->alamat_stlh_menikah;
            $data['pekerjaan_suami'] = $calonSuami->pekerjaan;
            $data['pendidikan_terakhir_suami'] = $calonSuami->pendidikan_terakhir;
            $data['agama_suami'] = $calonSuami->agama;

            // Data keluarga calon suami
            if ($calonSuami->keluarga) {
                $data['nama_ayah_suami'] = $calonSuami->keluarga->nama_ayah;
                $data['agama_ayah_suami'] = $calonSuami->keluarga->agama_ayah;
                $data['pekerjaan_ayah_suami'] = $calonSuami->keluarga->pekerjaan_ayah;
                $data['alamat_ayah_suami'] = $calonSuami->keluarga->alamat_ayah;
                $data['nama_ibu_suami'] = $calonSuami->keluarga->nama_ibu;
                $data['agama_ibu_suami'] = $calonSuami->keluarga->agama_ibu;
                $data['pekerjaan_ibu_suami'] = $calonSuami->keluarga->pekerjaan_ibu;
                $data['alamat_ibu_suami'] = $calonSuami->keluarga->alamat_ibu;
            }

            // Data lingkungan calon suami
            $data['lingkungan_suami_id'] = $calonSuami->lingkungan_id;
            $data['nama_lingkungan_suami'] = $calonSuami->nama_lingkungan;
            $data['wilayah_suami'] = $calonSuami->wilayah;
            $data['paroki_suami'] = $calonSuami->paroki;
            $data['nama_ketua_suami'] = $calonSuami->nama_ketua;
            $data['ttd_ketua_suami'] = $calonSuami->ttd_ketua;
            $data['ketua_lingkungan_id'] = $calonSuami->ketua_lingkungan_id;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Handle tanda tangan
        $tandaTanganFields = [
            'ttd_calon_istri',
            'ttd_calon_suami',
            'ttd_ketua_istri',
            'ttd_ketua_suami'
        ];

        foreach ($tandaTanganFields as $field) {
            if (isset($this->data[$field])) { 
                if (Str::startsWith($this->data[$field], 'data:image')) {
                    // Jika tanda tangan baru diupload
                    $this->data[$field] = $this->saveSignature($this->data[$field]);
                } elseif (empty($this->data[$field])) {
                    // Jika tanda tangan dihapus
                    $this->data[$field] = null;
                }
            }
        }

        // Update data calon istri
        $calonIstri = CalonPasangan::find($this->data['calon_istri_id']);
        if ($calonIstri) {
            $this->updateCalonPasangan($calonIstri, 'istri');
        }

        // Update data calon suami
        $calonSuami = CalonPasangan::find($this->data['calon_suami_id']);
        if ($calonSuami) {
            $this->updateCalonPasangan($calonSuami, 'suami');
        }
    }

    protected function updateCalonPasangan(CalonPasangan $calon, string $type): void
    {
        $prefix = $type === 'istri' ? 'istri' : 'suami';

        // Update user
        $user = $calon->user;
        $user->update([
            'name' => $this->data["nama_{$prefix}"],
            'email' => $this->data["akun_email_{$prefix}"],
            'tempat_lahir' => $this->data["tempat_lahir_{$prefix}"],
            'tgl_lahir' => $this->data["tgl_lahir_{$prefix}"],
            'telepon' => $this->data["telepon_{$prefix}"],
            'tanda_tangan' => $this->data["ttd_calon_{$prefix}"] ?? $user->tanda_tangan,
        ]);

        // Update detail user
        $detailUser = $user->detailUser;
        if ($detailUser) {
            $detailUser->update([
                'tempat_baptis' => $this->data["tempat_baptis_{$prefix}"],
                'tgl_baptis' => $this->data["tgl_baptis_{$prefix}"],
                'alamat' => $this->data["alamat_sekarang_{$prefix}"],
            ]);
        }

        // Update keluarga
        $keluarga = $calon->keluarga;
        if ($keluarga) {
            $keluarga->update([
                'nama_ayah' => $this->data["nama_ayah_{$prefix}"],
                'agama_ayah' => $this->data["agama_ayah_{$prefix}"],
                'pekerjaan_ayah' => $this->data["pekerjaan_ayah_{$prefix}"],
                'alamat_ayah' => $this->data["alamat_ayah_{$prefix}"],
                'nama_ibu' => $this->data["nama_ibu_{$prefix}"],
                'agama_ibu' => $this->data["agama_ibu_{$prefix}"],
                'pekerjaan_ibu' => $this->data["pekerjaan_ibu_{$prefix}"],
                'alamat_ibu' => $this->data["alamat_ibu_{$prefix}"],
            ]);
        }

        // Update calon pasangan
        $lingkungan = Lingkungan::find($this->data["lingkungan_{$prefix}_id"]);
        $ketuaLingkungan = $lingkungan 
            ? $lingkungan->ketuaLingkungans()->where('aktif', true)->first()
            : null;

        $calon->update([
            'lingkungan_id' => $this->data["lingkungan_{$prefix}_id"],
            'ketua_lingkungan_id' => $ketuaLingkungan ? $ketuaLingkungan->id : null,
            'nama_lingkungan' => $this->data["nama_lingkungan_{$prefix}"],
            'nama_ketua' => $this->data["nama_ketua_{$prefix}"],
            'wilayah' => $this->data["wilayah_{$prefix}"],
            'paroki' => $this->data["paroki_{$prefix}"],
            'alamat_stlh_menikah' => $this->data["alamat_setelah_menikah_{$prefix}"],
            'pekerjaan' => $this->data["pekerjaan_{$prefix}"],
            'pendidikan_terakhir' => $this->data["pendidikan_terakhir_{$prefix}"],
            'agama' => $this->data["agama_{$prefix}"],
            'ttd_ketua' => $this->data["ttd_ketua_{$prefix}"] ?? $calon->ttd_ketua,
        ]);
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