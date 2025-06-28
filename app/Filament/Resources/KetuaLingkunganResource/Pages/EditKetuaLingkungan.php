<?php

namespace App\Filament\Resources\KetuaLingkunganResource\Pages;

use App\Filament\Resources\KetuaLingkunganResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditKetuaLingkungan extends EditRecord
{
    protected static string $resource = KetuaLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return KetuaLingkunganResource::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Cek apakah status aktif diubah menjadi tidak aktif
        if ($this->record->aktif === false) {
            // Ambil user yang terkait dengan ketua lingkungan ini
            $user = User::find($this->record->user_id);
            
            // Jika user ditemukan
            if ($user) {
                // Hapus role ketua_lingkungan
                $user->removeRole('ketua_lingkungan');
                
                // Tambahkan role umat jika belum memilikinya
                if (!$user->hasRole('umat')) {
                    $user->assignRole('umat');
                }
                
                // Simpan perubahan
                $user->save();
            }
        } elseif ($this->record->aktif === true) {
            // Jika status aktif diubah menjadi aktif
            $user = User::find($this->record->user_id);
            
            // Jika user ditemukan
            if ($user) {
                // Tambahkan role ketua_lingkungan jika belum memilikinya
                if (!$user->hasRole('ketua_lingkungan')) {
                    $user->assignRole('ketua_lingkungan');
                }
                
                // Simpan perubahan
                $user->save();
            }
        }
    }
}