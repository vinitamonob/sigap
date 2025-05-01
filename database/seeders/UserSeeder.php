<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat roles jika belum ada
        $roles = ['super_admin', 'umat', 'ketua_lingkungan', 'paroki'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Membuat user dengan role super_admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'alamat' => 'Jl. Super Admin No. 1',
            'telepon' => '081298765432',
            'nama_lingkungan' => null,
            'tanda_tangan' => null,
            'remember_token' => Str::random(10),
        ])->assignRole('super_admin');

        // Membuat admin paroki dengan role paroki
        User::create([
            'name' => 'Admin Paroki',
            'email' => 'paroki@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'alamat' => 'Jl. Gereja Utama No. 1',
            'telepon' => '081234567890',
            'nama_lingkungan' => null,
            'tanda_tangan' => null,
            'remember_token' => Str::random(10),
        ])->assignRole('paroki');

        // Membuat ketua lingkungan
        $lingkungan = ['St Petrus', 'St Paulus', 'St Yohanes', 'St Maria', 'St Yosef'];
        
        foreach ($lingkungan as $index => $nama) {
            User::create([
                'name' => 'Ketua ' . $nama,
                'email' => strtolower(str_replace(' ', '.', $nama)) . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'alamat' => 'Jl. Lingkungan ' . $nama . ' No. ' . ($index + 1),
                'telepon' => '08' . rand(1000000000, 9999999999),
                'nama_lingkungan' => $nama,
                'tanda_tangan' => null,
                'remember_token' => Str::random(10),
            ])->assignRole('ketua_lingkungan');
        }

        // Membuat umat dengan pola email mengikuti nama user
        $faker = \Faker\Factory::create('id_ID');

        for ($i = 0; $i < 3; $i++) {
            // Membuat nama hanya 2 kata
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $name = $firstName . ' ' . $lastName;

            // Membuat email berdasarkan nama dengan pola nama menjadi lowercase dan dipisahkan dengan titik
            $email = strtolower(str_replace(' ', '.', $name)) . '@example.com';

            // Membuat user dengan role 'umat'
            User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'alamat' => $faker->address(),
                'telepon' => '08' . rand(1000000000, 9999999999),
                'nama_lingkungan' => $faker->randomElement($lingkungan), // Mengambil lingkungan secara acak
                'tanda_tangan' => null,
                'remember_token' => Str::random(10),
            ])->assignRole('umat');
        }

    }
}
