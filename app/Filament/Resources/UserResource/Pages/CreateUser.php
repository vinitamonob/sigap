<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Lingkungan;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate()
    {
        Lingkungan::create([
            'kode' => $this->data['kode'],
            'nama_lingkungan' => $this->data['nama_lingkungan'],
            'user_id' => $this->record->id
        ]);
    }
    
    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
