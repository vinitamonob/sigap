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

        $lingkungans = ['St Petrus', 'St Paulus', 'St Yohanes', 'St Maria', 'St Yosef'];

        foreach ($lingkungans as $nama) {
            DB::table('lingkungans')->insert([
                'nama_lingkungan' => $nama,
                'kode' => strtoupper(Str::slug(Str::limit(str_replace('St ', '', $nama), 4, ''), '')),
                'wilayah' => $faker->city,
                'paroki' => 'St. Stephanus Cilacap',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
