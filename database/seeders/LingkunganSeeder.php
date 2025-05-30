<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class LingkunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Daftar nama lingkungan
        $lingkungans = ['St Petrus', 'St Paulus', 'St Yohanes', 'St Maria', 'St Yosef'];

        // Daftar wilayah
        $wilayahs = ['Cilacap Tengah', 'Cilacap Utara', 'Cilacap Selatan', 'Jeruk Legi', 'Cilacap Kota'];

        foreach ($lingkungans as $nama) {
            DB::table('lingkungans')->insert([
                'nama_lingkungan' => $nama,
                'kode' => strtoupper(Str::limit(str_replace('St ', '', $nama), 4, '')), // Kode lingkungan
                'wilayah' => $faker->randomElement($wilayahs), // Wilayah dipilih secara acak
                'paroki' => 'St. Stephanus Cilacap', // Paroki default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}