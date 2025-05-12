<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lingkungan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KetuaLingkunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua pengguna dengan peran 'ketua_lingkungan'
        $users = User::role('ketua_lingkungan')->get();

        foreach ($users as $user) {
            // Ekstrak nama lingkungan dari nama pengguna
            // Misalnya, dari 'Ketua St Petrus' menjadi 'St Petrus'
            $namaLingkungan = trim(str_replace('Ketua', '', $user->name));

            // Cari lingkungan yang sesuai berdasarkan nama
            $lingkungan = Lingkungan::where('nama_lingkungan', $namaLingkungan)->first();

            // Jika lingkungan ditemukan, buat entri di tabel 'ketua_lingkungans'
            if ($lingkungan) {
                DB::table('ketua_lingkungans')->insert([
                    'user_id' => $user->id,
                    'lingkungan_id' => $lingkungan->id,
                    'mulai_jabatan' => Carbon::now()->subYear(), // Mulai jabatan 1 tahun yang lalu
                    'akhir_jabatan' => Carbon::now()->addYears(2), // Akhir jabatan 2 tahun dari sekarang
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}