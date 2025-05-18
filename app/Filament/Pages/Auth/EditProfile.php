<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\Keluarga;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        $user = User::where('id', Auth::user()->id)
            ->with(['detailUser.lingkungan', 'detailUser.keluarga'])
            ->first();

        $isAdminOrParoki = $user->hasRole('super_admin') || $user->hasRole('paroki');
        $isAdminOrParokiOrKetua = $user->hasRole('super_admin') || $user->hasRole('paroki') || $user->hasRole('ketua_lingkungan');

        return $form
            ->schema([
                Section::make('Informasi Pribadi')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->required(false)
                            ->hidden(fn () => $user->hasRole('super_admin')),
                        DatePicker::make('tgl_lahir')
                            ->label('Tanggal Lahir')
                            ->required(false)
                            ->hidden(fn () => $user->hasRole('super_admin')),
                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Pria' => 'Pria',
                                'Wanita' => 'Wanita',
                            ])
                            ->required(false)
                            ->hidden(fn () => $user->hasRole('super_admin')),
                        $this->getTeleponFormComponent(),
                        $this->getNamaLingkunganFormComponent()
                            ->hidden($isAdminOrParoki),
                        Textarea::make('detail_user.alamat')
                            ->label('Alamat Lengkap')
                            ->required(false)
                            ->hidden($isAdminOrParoki),
                    ])->columns(2),
                
                Section::make('Informasi Baptis')
                    ->schema([
                        TextInput::make('detail_user.nama_baptis')
                            ->label('Nama Baptis')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('detail_user.tempat_baptis')
                            ->label('Tempat Baptis')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        DatePicker::make('detail_user.tgl_baptis')
                            ->label('Tanggal Baptis')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('detail_user.no_baptis')
                            ->label('Nomor Baptis')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
                    ->columns(2)
                    ->hidden($isAdminOrParokiOrKetua),
                
                Section::make('Informasi Keluarga')
                    ->schema([
                        TextInput::make('keluarga.nama_ayah')
                            ->label('Nama Ayah')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('keluarga.nama_ibu')
                            ->label('Nama Ibu')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('keluarga.agama_ayah')
                            ->label('Agama Ayah')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('keluarga.agama_ibu')
                            ->label('Agama Ibu')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('keluarga.pekerjaan_ayah')
                            ->label('Pekerjaan Ayah')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        TextInput::make('keluarga.pekerjaan_ibu')
                            ->label('Pekerjaan Ibu')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        Textarea::make('keluarga.alamat_ayah')
                            ->label('Alamat Ayah')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        Textarea::make('keluarga.alamat_ibu')
                            ->label('Alamat Ibu')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
                    ->columns(2)
                    ->hidden($isAdminOrParokiOrKetua),
                
                Section::make('Keamanan & Tanda Tangan')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        SignaturePad::make('tanda_tangan')
                            ->label('Tanda Tangan')
                            ->required(false)
                            ->hidden(fn () => $user->hasRole('super_admin')),
                        SignaturePad::make('keluarga.ttd_ayah')
                            ->label('Tanda Tangan Ayah')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                        SignaturePad::make('keluarga.ttd_ibu')
                            ->label('Tanda Tangan Ibu')
                            ->required(false)
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
            ]);
    }

    protected function getTeleponFormComponent()
    {
        return TextInput::make('telepon')
            ->label('Nomor Telepon/HP')
            ->required(false);
    }

    protected function getNamaLingkunganFormComponent()
    {
        return Select::make('detail_user.lingkungan_id')
            ->label('Lingkungan/Stasi')
            ->options(Lingkungan::pluck('nama_lingkungan', 'id')->toArray())
            ->searchable()
            ->required(false);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $signaturePath = public_path('storage/signatures');
        if (!File::exists($signaturePath)) {
            File::makeDirectory($signaturePath, 0755, true);
        }
        $user = User::where('id', Auth::user()->id)->first();
        $isAdminOrParokiOrKetua = $user->hasRole('super_admin') || $user->hasRole('paroki') || $user->hasRole('ketua-lingkungan');

        // Handle user tanda tangan
        if (isset($data['tanda_tangan'])) {
            $image = $data['tanda_tangan'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10).'.'.'png';
            File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
            $data['tanda_tangan'] = 'storage/signatures/' . $imageName;
        }

        // Hanya proses tanda tangan keluarga jika bukan admin/paroki/ketua
        if (!$isAdminOrParokiOrKetua && isset($data['keluarga'])) {
            // Handle tanda tangan ayah
            if (isset($data['keluarga']['ttd_ayah'])) {
                $image = $data['keluarga']['ttd_ayah'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'ayah_'.Str::random(10).'.'.'png';
                File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
                $data['keluarga']['ttd_ayah'] = 'storage/signatures/' . $imageName;
            }

            // Handle tanda tangan ibu
            if (isset($data['keluarga']['ttd_ibu'])) {
                $image = $data['keluarga']['ttd_ibu'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'ibu_'.Str::random(10).'.'.'png';
                File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
                $data['keluarga']['ttd_ibu'] = 'storage/signatures/' . $imageName;
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $isAdminOrParoki = $record->hasRole('super_admin') || $record->hasRole('paroki');
        $isAdminOrParokiOrKetua = $record->hasRole('super_admin') || $record->hasRole('paroki') || $record->hasRole('ketua_lingkungan');

        // Update user data
        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tgl_lahir' => $data['tgl_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'tanda_tangan' => $data['tanda_tangan'] ?? null,
        ]);

        // Hanya update detail user jika bukan admin/paroki
        if (!$isAdminOrParoki) {
            $detailUserData = [
                'nama_baptis' => $data['detail_user']['nama_baptis'] ?? null,
                'tempat_baptis' => $data['detail_user']['tempat_baptis'] ?? null,
                'tgl_baptis' => $data['detail_user']['tgl_baptis'] ?? null,
                'no_baptis' => $data['detail_user']['no_baptis'] ?? null,
                'alamat' => $data['detail_user']['alamat'] ?? null,
                'lingkungan_id' => $data['detail_user']['lingkungan_id'] ?? null,
            ];

            $record->detailUser()->updateOrCreate(
                ['user_id' => $record->id],
                $detailUserData
            );

            // Handle keluarga data hanya jika bukan admin/paroki/ketua
            if (!$isAdminOrParokiOrKetua && isset($data['keluarga'])) {
                $keluargaData = [
                    'nama_ayah' => $data['keluarga']['nama_ayah'] ?? null,
                    'agama_ayah' => $data['keluarga']['agama_ayah'] ?? null,
                    'pekerjaan_ayah' => $data['keluarga']['pekerjaan_ayah'] ?? null,
                    'alamat_ayah' => $data['keluarga']['alamat_ayah'] ?? null,
                    'nama_ibu' => $data['keluarga']['nama_ibu'] ?? null,
                    'agama_ibu' => $data['keluarga']['agama_ibu'] ?? null,
                    'pekerjaan_ibu' => $data['keluarga']['pekerjaan_ibu'] ?? null,
                    'alamat_ibu' => $data['keluarga']['alamat_ibu'] ?? null,
                    'ttd_ayah' => $data['keluarga']['ttd_ayah'] ?? null,
                    'ttd_ibu' => $data['keluarga']['ttd_ibu'] ?? null,
                ];

                // Cek apakah keluarga sudah ada
                if ($record->detailUser && $record->detailUser->keluarga) {
                    // Update keluarga yang sudah ada
                    $record->detailUser->keluarga()->update($keluargaData);
                } else {
                    // Buat keluarga baru dan hubungkan ke detail_user
                    $keluarga = Keluarga::create($keluargaData);
                    $record->detailUser()->update(['keluarga_id' => $keluarga->id]);
                }
            }
        }

        return $record;
    }
}