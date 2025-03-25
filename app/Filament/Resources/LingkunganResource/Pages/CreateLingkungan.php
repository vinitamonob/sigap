<?php

namespace App\Filament\Resources\LingkunganResource\Pages;

use Filament\Actions;
use App\Models\Lingkungan;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LingkunganResource;

class CreateLingkungan extends CreateRecord
{
    protected static string $resource = LingkunganResource::class;

    protected function getRedirectUrl(): string
    {
        return LingkunganResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Memastikan user_id yang dipilih hanya bisa digunakan untuk 1 lingkungan
        $existingLingkungan = Lingkungan::where('user_id', $data['user_id'])->first();
        
        if ($existingLingkungan) {
            // Jika user sudah menjadi ketua lingkungan, tampilkan error
            $this->halt();
            $this->notify('danger', 'User ini sudah menjadi ketua lingkungan lain.');
        }
        
        return $data;
    }
    
}
