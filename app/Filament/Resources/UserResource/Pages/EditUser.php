<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Lingkungan;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $lingkungan = Lingkungan::where('user_id', $data['id'])->first();
        $data['kode'] = $lingkungan->kode ?? '';
        $data['nama_lingkungan'] = $lingkungan->nama_lingkungan ?? '';

        return $data;
    }

    protected function afterSave()
    {
        Lingkungan::where('user_id', $this->record->id)
            ->update([
                'kode' => $this->data['kode'],
                'nama_lingkungan' => $this->data['nama_lingkungan'],
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
