<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lingkungan;
use App\Models\User;
use Faker\Factory as Faker;
class LingkunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar nama lingkungan
        $lingkungan = ['St Petrus', 'St Paulus', 'St Yohanes', 'St Maria', 'St Yosef'];

        // Loop untuk setiap nama lingkungan
        foreach ($lingkungan as $namaLingkungan) {
            // Ambil user dengan role 'ketua_lingkungan' dan nama yang sesuai dengan nama lingkungan
            $user = User::whereHas('roles', function ($query) {
                $query->where('name', 'ketua_lingkungan');
            })->where('nama_lingkungan', $namaLingkungan)->first();

            // Jika user ditemukan, buat lingkungan
            if ($user) {
                // Buat kode unik (berisi inisial nama lingkungan dengan panjang 3/4 huruf)
                $kode = strtoupper(substr(str_replace(' ', '', $namaLingkungan), 0, 4));

                // Insert ke database
                Lingkungan::create([
                    'kode' => $kode,                         // Kode unik (inisial nama lingkungan)
                    'nama_lingkungan' => $namaLingkungan,    // Nama lingkungan
                    'user_id' => $user->id,                  // ID user yang sesuai
                ]);
            }
        }
    }
}
