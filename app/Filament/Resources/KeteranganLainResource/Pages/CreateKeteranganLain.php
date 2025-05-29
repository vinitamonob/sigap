<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Models\User;
use App\Models\Surat;
use App\Models\DetailUser;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\KeteranganLainResource;

class CreateKeteranganLain extends CreateRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // If user is selected from database
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            
            if ($user) {
                // Update user data if different from form
                $userUpdates = [];
                if ($user->name !== $data['nama_lengkap']) {
                    $userUpdates['name'] = $data['nama_lengkap'];
                }
                if ($user->email !== $data['akun_email']) {
                    $userUpdates['email'] = $data['akun_email'];
                }
                if ($user->tempat_lahir !== $data['tempat_lahir']) {
                    $userUpdates['tempat_lahir'] = $data['tempat_lahir'];
                }
                if ($user->tgl_lahir != $data['tgl_lahir']) {
                    $userUpdates['tgl_lahir'] = $data['tgl_lahir'];
                }
                if ($user->telepon !== $data['telepon']) {
                    $userUpdates['telepon'] = $data['telepon'];
                }
                
                if (!empty($userUpdates)) {
                    $user->update($userUpdates);
                }
                
                // Update or create detail user
                $detailUser = DetailUser::firstOrNew(['user_id' => $user->id]);
                $detailUser->lingkungan_id = $data['lingkungan_id'];
                $detailUser->alamat = $data['alamat'];
                $detailUser->save();
            }
        } 
        // If user is not selected (manual input)
        else {
            // Create new user
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['akun_email'],
                'password' => bcrypt('12345678'), // Default password
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'telepon' => $data['telepon'],
            ]);

            // Assign role 'umat'
            $user->assignRole('umat');

            // Create detail user
            DetailUser::create([
                'user_id' => $user->id,
                'lingkungan_id' => $data['lingkungan_id'],
                'alamat' => $data['alamat'],
            ]);

            $data['user_id'] = $user->id;
        }

        // Create the KeteranganLain record
        $record = static::getModel()::create($data);
        
        // Create related Surat record
        $surat = Surat::create([
            'user_id' => $data['user_id'],
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'keterangan_lain',
            'perihal' => 'Keterangan Lain',
            'tgl_surat' => $data['tgl_surat'],
            'status' => 'menunggu',
        ]);
        
        // Link the surat to keterangan lain record
        $record->update(['surat_id' => $surat->id]);
        
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}