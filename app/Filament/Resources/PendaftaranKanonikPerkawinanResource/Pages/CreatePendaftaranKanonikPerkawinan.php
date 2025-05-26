<?php

namespace App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

use App\Models\User;
use App\Models\Surat;
use App\Models\Keluarga;
use App\Models\DetailUser;
use App\Models\CalonPasangan;
use App\Models\Lingkungan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource;

class CreatePendaftaranKanonikPerkawinan extends CreateRecord
{
    protected static string $resource = PendaftaranKanonikPerkawinanResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Proses tanda tangan
        $tandaTanganFields = [
            'ttd_calon_istri',
            'ttd_calon_suami',
            'ttd_ketua_istri',
            'ttd_ketua_suami'
        ];

        foreach ($tandaTanganFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $image = $data[$field];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.png';
                File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
                $data[$field] = 'storage/signatures/' . $imageName;
            }
        }

        // Buat user baru untuk calon istri
        $userIstri = User::create([
            'name' => $data['nama_istri'],
            'email' => $data['akun_email_istri'],
            'password' => bcrypt('12345678'),
            'jenis_kelamin' => 'Wanita',
            'tempat_lahir' => $data['tempat_lahir_istri'],
            'tgl_lahir' => $data['tgl_lahir_istri'],
            'telepon' => $data['telepon_istri'],
            'tanda_tangan' => $data['ttd_calon_istri'],
        ]);

        // Assign role 'umat' untuk calon istri
        $userIstri->assignRole('umat');

        // Buat keluarga baru untuk calon istri
        $keluargaIstri = Keluarga::create([
            'nama_ayah' => $data['nama_ayah_istri'],
            'agama_ayah' => $data['agama_ayah_istri'],
            'pekerjaan_ayah' => $data['pekerjaan_ayah_istri'],
            'alamat_ayah' => $data['alamat_ayah_istri'],
            'nama_ibu' => $data['nama_ibu_istri'],
            'agama_ibu' => $data['agama_ibu_istri'],
            'pekerjaan_ibu' => $data['pekerjaan_ibu_istri'],
            'alamat_ibu' => $data['alamat_ibu_istri'],
        ]);

        // Buat detail user untuk calon istri
        $detailUserIstri = DetailUser::create([
            'user_id' => $userIstri->id,
            'lingkungan_id' => $data['lingkungan_istri_id'],
            'keluarga_id' => $keluargaIstri->id,
            'tempat_baptis' => $data['tempat_baptis_istri'],
            'tgl_baptis' => $data['tgl_baptis_istri'],
            'alamat' => $data['alamat_sekarang_istri'],
        ]);

        // Untuk calon istri
        $lingkunganIstri = Lingkungan::find($data['lingkungan_istri_id']);
        $ketuaLingkunganIstriId = $lingkunganIstri 
            ? $lingkunganIstri->ketuaLingkungans()->where('aktif', true)->first()?->id 
            : null;

        $calonPasanganCwe = [
            'user_id' => $userIstri->id,
            'lingkungan_id' => $data['lingkungan_istri_id'],
            'ketua_lingkungan_id' => $ketuaLingkunganIstriId,
            'nama_lingkungan' => $data['nama_lingkungan_istri'] ?? ($lingkunganIstri->nama_lingkungan ?? null),
            'nama_ketua' => $data['nama_ketua_istri'],
            'wilayah' => $data['wilayah_istri'] ?? ($lingkunganIstri->wilayah ?? null),
            'paroki' => $data['paroki_istri'] ?? ($lingkunganIstri->paroki ?? null),
            'keluarga_id' => $keluargaIstri->id,
            'alamat_stlh_menikah' => $data['alamat_setelah_menikah_istri'],
            'pekerjaan' => $data['pekerjaan_istri'],
            'pendidikan_terakhir' => $data['pendidikan_terakhir_istri'],
            'agama' => $data['agama_istri'],
            'jenis_kelamin' => 'Wanita',
        ];

        // Buat calon pasangan untuk istri
        $calonIstri = CalonPasangan::create($calonPasanganCwe);

        $data['calon_istri_id'] = $calonIstri->id;

        // Buat user baru untuk calon suami
        $userSuami = User::create([
            'name' => $data['nama_suami'],
            'email' => $data['akun_email_suami'],
            'password' => bcrypt('12345678'),
            'jenis_kelamin' => 'Pria',
            'tempat_lahir' => $data['tempat_lahir_suami'],
            'tgl_lahir' => $data['tgl_lahir_suami'],
            'telepon' => $data['telepon_suami'],
            'tanda_tangan' => $data['ttd_calon_suami'],
        ]);

        // Assign role 'umat' untuk calon suami
        $userSuami->assignRole('umat');

        // Buat keluarga baru untuk calon suami
        $keluargaSuami = Keluarga::create([
            'nama_ayah' => $data['nama_ayah_suami'],
            'agama_ayah' => $data['agama_ayah_suami'],
            'pekerjaan_ayah' => $data['pekerjaan_ayah_suami'],
            'alamat_ayah' => $data['alamat_ayah_suami'],
            'nama_ibu' => $data['nama_ibu_suami'],
            'agama_ibu' => $data['agama_ibu_suami'],
            'pekerjaan_ibu' => $data['pekerjaan_ibu_suami'],
            'alamat_ibu' => $data['alamat_ibu_suami'],
        ]);

        // Buat detail user untuk calon suami
        $detailUserSuami = DetailUser::create([
            'user_id' => $userSuami->id,
            'lingkungan_id' => $data['lingkungan_suami_id'],
            'keluarga_id' => $keluargaSuami->id,
            'tempat_baptis' => $data['tempat_baptis_suami'],
            'tgl_baptis' => $data['tgl_baptis_suami'],
            'alamat' => $data['alamat_sekarang_suami'],
        ]);

        // Untuk calon suami
        $lingkunganSuami = Lingkungan::find($data['lingkungan_suami_id']);
        $ketuaLingkunganSuamiId = $lingkunganSuami
            ? $lingkunganSuami->ketuaLingkungans()->where('aktif', true)->first()?->id
            : null;

        $calonPasanganCwo = [
            'user_id' => $userSuami->id,
            'lingkungan_id' => $data['lingkungan_suami_id'],
            'ketua_lingkungan_id' => $ketuaLingkunganSuamiId,
            'nama_lingkungan' => $data['nama_lingkungan_suami'] ?? ($lingkunganSuami->nama_lingkungan ?? null),
            'nama_ketua' => $data['nama_ketua_suami'],
            'wilayah' => $data['wilayah_suami'] ?? ($lingkunganSuami->wilayah ?? null),
            'paroki' => $data['paroki_suami'] ?? ($lingkunganSuami->paroki ?? null),
            'keluarga_id' => $keluargaSuami->id,
            'alamat_stlh_menikah' => $data['alamat_setelah_menikah_suami'],
            'pekerjaan' => $data['pekerjaan_suami'],
            'pendidikan_terakhir' => $data['pendidikan_terakhir_suami'],
            'agama' => $data['agama_suami'],
            'jenis_kelamin' => 'Pria',
        ];

        // Buat calon pasangan untuk suami
        $calonSuami = CalonPasangan::create($calonPasanganCwo);
        // dd($calonPasanganCwe, $calonPasanganCwo);

        $data['calon_suami_id'] = $calonSuami->id;

        // Buat record pendaftaran kanonik perkawinan
        $record = static::getModel()::create($data);
        
        // Buat surat terkait - gunakan lingkungan istri atau suami yang memiliki lingkungan_id
        $lingkunganId = $data['lingkungan_istri_id'] ?? $data['lingkungan_suami_id'];
        
        $surat = Surat::create([
            'user_id' => null,
            'lingkungan_id' => $lingkunganId,
            'jenis_surat' => 'pendaftaran_perkawinan',
            'perihal' => 'Pendaftaran Kanonik & Perkawinan',
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