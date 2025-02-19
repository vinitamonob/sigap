<?php

namespace App\Filament\Resources\LingkunganResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LingkunganResource;

class CreateLingkungan extends CreateRecord
{
    protected static string $resource = LingkunganResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Create user first
    //     $user = User::create([
    //         'name' => $data['user']['name'],
    //         'email' => $data['user']['email'],
    //         'password' => Hash::make($data['user']['password'] ?? '12345678'),
    //     ]);
    //     // Assign role 
    //     $user->assignRole('ketua_lingkungan');
    //     // Remove user data from $data array and set user_id
    //     unset($data['user']);
    //     $data['user_id'] = $user->id;

    //     return $data;
    // }

    protected function getRedirectUrl(): string
    {
        return LingkunganResource::getUrl('index');
    }
}
