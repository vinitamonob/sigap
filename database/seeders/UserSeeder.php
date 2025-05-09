<?php

namespace Database\Seeders;

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
            'remember_token' => Str::random(10),
        ])->assignRole('super_admin');

        // Membuat admin paroki
        User::create([
            'name' => 'Admin Paroki',
            'email' => 'paroki@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
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
                'remember_token' => Str::random(10),
            ])->assignRole('ketua_lingkungan');
        }

        // Membuat role Umat
        for ($i = 1; $i <= 3; $i++) {
            $name = 'Umat' . $i;
            $email = strtolower($name) . '@example.com';
            User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'remember_token' => Str::random(10),
            ])->assignRole('umat');
        }
    }
}
