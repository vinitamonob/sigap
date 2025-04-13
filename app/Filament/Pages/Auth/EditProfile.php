<?php

namespace App\Filament\Pages\Auth;

use App\Models\Lingkungan;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class EditProfile extends BaseEditProfile
{

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getAlamatFormComponent(),
                $this->getTeleponFormComponent(),
                $this->getNamaLingkunganFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                SignaturePad::make('tanda_tangan')
                    ->label('Tanda Tangan')
                    ->required(false)
            ]);
    }

    protected function getAlamatFormComponent()
    {
        return TextInput::make('alamat')
            ->label('Alamat Lengkap')
            ->required(false);
    }

    protected function getTeleponFormComponent()
    {
        return TextInput::make('telepon')
            ->label('Telepon')
            ->required(false);
    }

    protected function getNamaLingkunganFormComponent()
    {
        return Select::make('nama_lingkungan')
            ->label('Nama Lingkungan / Stasi')
            ->options(Lingkungan::pluck('nama_lingkungan', 'nama_lingkungan')->toArray())
            ->searchable()
            ->required(false);
    }

    protected function mutateFormDataBeforeSave($data): array
    {
        $image = $data['tanda_tangan'];  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));

        $data['tanda_tangan'] = $imageName;
        // dd($data);
        return $data;
    }
}
