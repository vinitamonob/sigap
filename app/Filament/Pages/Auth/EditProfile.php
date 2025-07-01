<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\Keluarga;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class EditProfile extends BaseEditProfile
{
    protected function getFormModel(): Model
    {
        return User::where('id', Auth::user()->id)
            ->with(['detailUser.lingkungan', 'detailUser.keluarga'])
            ->first();
    }

    public function mount(): void
    {
        parent::mount();
        
        // Fill form with existing data
        $this->fillForm();
    }
    
    public function fillForm(): void
    {
        $user = $this->getFormModel();
        
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'tempat_lahir' => $user->tempat_lahir,
            'tgl_lahir' => $user->tgl_lahir,
            'jenis_kelamin' => $user->jenis_kelamin,
            'telepon' => $user->telepon,
            'tanda_tangan' => $user->tanda_tangan,
        ];
        
        if ($user->detailUser) {
            $data['detailUser'] = [
                'nama_baptis' => $user->detailUser->nama_baptis,
                'tempat_baptis' => $user->detailUser->tempat_baptis,
                'tgl_baptis' => $user->detailUser->tgl_baptis,
                'no_baptis' => $user->detailUser->no_baptis,
                'alamat' => $user->detailUser->alamat,
                'lingkungan_id' => $user->detailUser->lingkungan_id,
            ];
            
            if ($user->detailUser->keluarga) {
                $data['detailUser']['keluarga'] = [
                    'nama_ayah' => $user->detailUser->keluarga->nama_ayah,
                    'agama_ayah' => $user->detailUser->keluarga->agama_ayah,
                    'pekerjaan_ayah' => $user->detailUser->keluarga->pekerjaan_ayah,
                    'alamat_ayah' => $user->detailUser->keluarga->alamat_ayah,
                    'ttd_ayah' => $user->detailUser->keluarga->ttd_ayah,
                    'nama_ibu' => $user->detailUser->keluarga->nama_ibu,
                    'agama_ibu' => $user->detailUser->keluarga->agama_ibu,
                    'pekerjaan_ibu' => $user->detailUser->keluarga->pekerjaan_ibu,
                    'alamat_ibu' => $user->detailUser->keluarga->alamat_ibu,
                    'ttd_ibu' => $user->detailUser->keluarga->ttd_ibu,
                ];
            }
        }
        
        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        $user = $this->getFormModel();
        $isAdminOrParoki = $user->hasRole('super_admin') || $user->hasRole('paroki');
        $isAdminOrParokiOrKetua = $user->hasRole('super_admin') || $user->hasRole('paroki') || $user->hasRole('ketua_lingkungan');

        return $form
            ->schema([
                Section::make('Informasi Pribadi')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getTempatLahirFormComponent(),
                        $this->getTanggalLahirFormComponent(),
                        $this->getJenisKelaminFormComponent(),
                        $this->getTeleponFormComponent(),
                        $this->getNamaLingkunganFormComponent()
                            ->hidden($isAdminOrParoki),
                        $this->getAlamatLengkapFormComponent()
                            ->hidden($isAdminOrParoki),
                    ])->columns(2),
                
                Section::make('Informasi Baptis')
                    ->schema([
                        $this->getNamaBaptisFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getTempatBaptisFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getTanggalBaptisFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getNomorBaptisFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
                    ->columns(2)
                    ->hidden($isAdminOrParokiOrKetua),
                
                Section::make('Informasi Keluarga')
                    ->schema([
                        $this->getNamaAyahFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getNamaIbuFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getAgamaAyahFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getAgamaIbuFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getPekerjaanAyahFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getPekerjaanIbuFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getAlamatAyahFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getAlamatIbuFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
                    ->columns(2)
                    ->hidden($isAdminOrParokiOrKetua),
                
                Section::make('Keamanan & Tanda Tangan')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getTandaTanganFormComponent(),
                        $this->getTandaTanganAyahFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                        $this->getTandaTanganIbuFormComponent()
                            ->hidden($isAdminOrParokiOrKetua),
                    ])
            ]);
    }

    protected function getTeleponFormComponent(): TextInput
    {
        return TextInput::make('telepon')
            ->label('Nomor Telepon/HP')
            ->required(false);
    }

    protected function getNamaLingkunganFormComponent(): Select
    {
        return Select::make('detailUser.lingkungan_id')
            ->label('Lingkungan/Stasi')
            ->options(Lingkungan::pluck('nama_lingkungan', 'id')->toArray())
            ->searchable()
            ->required(false);
    }

    protected function getTempatLahirFormComponent(): TextInput
    {
        $user = User::where('id', Auth::user()->id)->first();
        return TextInput::make('tempat_lahir')
            ->label('Tempat Lahir')
            ->required(false)
            ->hidden(fn () => $user->hasRole('super_admin'));
    }

    protected function getTanggalLahirFormComponent(): DatePicker
    {
        $user = User::where('id', Auth::user()->id)->first();
        return DatePicker::make('tgl_lahir')
            ->label('Tanggal Lahir')
            ->maxDate(now())
            ->required(false)
            ->hidden(fn () => $user->hasRole('super_admin'));
    }

    protected function getJenisKelaminFormComponent(): Select
    {
        $user = User::where('id', Auth::user()->id)->first();
        return Select::make('jenis_kelamin')
            ->label('Jenis Kelamin')
            ->options([
                'Pria' => 'Pria',
                'Wanita' => 'Wanita',
            ])
            ->required(false)
            ->hidden(fn () => $user->hasRole('super_admin'));
    }

    protected function getAlamatLengkapFormComponent(): Textarea
    {
        return Textarea::make('detailUser.alamat')
            ->label('Alamat Lengkap')
            ->required(false);
    }

    protected function getNamaBaptisFormComponent(): TextInput
    {
        return TextInput::make('detailUser.nama_baptis')
            ->label('Nama Baptis')
            ->required(false);
    }

    protected function getTempatBaptisFormComponent(): TextInput
    {
        return TextInput::make('detailUser.tempat_baptis')
            ->label('Tempat Baptis')
            ->required(false);
    }

    protected function getTanggalBaptisFormComponent(): DatePicker
    {
        return DatePicker::make('detailUser.tgl_baptis')
            ->label('Tanggal Baptis')
            ->minDate(now())
            ->required(false);
    }

    protected function getNomorBaptisFormComponent(): TextInput
    {
        return TextInput::make('detailUser.no_baptis')
            ->label('Nomor Baptis')
            ->required(false);
    }

    protected function getNamaAyahFormComponent(): TextInput
    {
        return TextInput::make('detailUser.keluarga.nama_ayah')
            ->label('Nama Ayah')
            ->required(false);
    }

    protected function getNamaIbuFormComponent(): TextInput
    {
        return TextInput::make('detailUser.keluarga.nama_ibu')
            ->label('Nama Ibu')
            ->required(false);
    }

    protected function getAgamaAyahFormComponent(): Select
    {
        return Select::make('detailUser.keluarga.agama_ayah')
            ->label('Agama Ayah')
            ->options([
                'Katolik' => 'Katolik',
                'Protestan' => 'Protestan',
                'Islam' => 'Islam',
                'Hindu' => 'Hindu',
                'Budha' => 'Budha',
            ])
            ->required(false);
    }

    protected function getAgamaIbuFormComponent(): Select
    {
        return Select::make('detailUser.keluarga.agama_ibu')
            ->label('Agama Ibu')
            ->options([
                'Katolik' => 'Katolik',
                'Protestan' => 'Protestan',
                'Islam' => 'Islam',
                'Hindu' => 'Hindu',
                'Budha' => 'Budha',
            ])
            ->required(false);
    }

    protected function getPekerjaanAyahFormComponent(): Select
    {
        return Select::make('detailUser.keluarga.pekerjaan_ayah')
            ->required(false)
            ->label('Pekerjaan Ayah')
            ->searchable()
            ->options([
                'PNS' => 'PNS',
                'TNI/Polri' => 'TNI/Polri',
                'Karyawan Swasta' => 'Karyawan Swasta',
                'Wiraswasta' => 'Wiraswasta',
                'Pedagang' => 'Pedagang',
                'Petani/Nelayan' => 'Petani/Nelayan',
                'Profesional (Dokter, Guru, dll)' => 'Profesional (Dokter, Guru, dll)',
                'Buruh' => 'Buruh/Tukang',
                'Ibu Rumah Tangga' => 'Ibu Rumah Tangga',
                'Pelajar/Mahasiswa' => 'Pelajar/Mahasiswa',
                'Lainnya' => 'Lainnya',
            ]);
    }

    protected function getPekerjaanIbuFormComponent(): Select
    {
        return Select::make('detailUser.keluarga.pekerjaan_ibu')
            ->required(false)
            ->label('Pekerjaan Ayah')
            ->searchable()
            ->options([
                'PNS' => 'PNS',
                'TNI/Polri' => 'TNI/Polri',
                'Karyawan Swasta' => 'Karyawan Swasta',
                'Wiraswasta' => 'Wiraswasta',
                'Pedagang' => 'Pedagang',
                'Petani/Nelayan' => 'Petani/Nelayan',
                'Profesional (Dokter, Guru, dll)' => 'Profesional (Dokter, Guru, dll)',
                'Buruh' => 'Buruh/Tukang',
                'Ibu Rumah Tangga' => 'Ibu Rumah Tangga',
                'Pelajar/Mahasiswa' => 'Pelajar/Mahasiswa',
                'Lainnya' => 'Lainnya',
            ]);
    }

    protected function getAlamatAyahFormComponent(): Textarea
    {
        return Textarea::make('detailUser.keluarga.alamat_ayah')
            ->label('Alamat Ayah')
            ->required(false);
    }

    protected function getAlamatIbuFormComponent(): Textarea
    {
        return Textarea::make('detailUser.keluarga.alamat_ibu')
            ->label('Alamat Ibu')
            ->required(false);
    }

    protected function getTandaTanganFormComponent(): SignaturePad
    {
        $user = User::where('id', Auth::user()->id)->first();
        return SignaturePad::make('tanda_tangan')
            ->label('Tanda Tangan')
            ->required(false)
            ->hidden(fn () => $user->hasRole('super_admin'))
            ->helperText(function () use ($user) {
                if ($user->tanda_tangan) {
                    return new HtmlString('<span class="text-yellow-600 dark:text-yellow-500">
                    Tanda tangan sudah tersimpan. Tanda tangan baru akan menggantikan yang lama.</span>');
                }
                return null;
            });
    }

    protected function getTandaTanganAyahFormComponent(): SignaturePad
    {
        $user = $this->getFormModel();
        return SignaturePad::make('detailUser.keluarga.ttd_ayah')
            ->label('Tanda Tangan Ayah')
            ->required(false)
            ->helperText(function () use ($user) {
                if ($user->detailUser?->keluarga?->ttd_ayah) {
                    return new HtmlString('<span class="text-yellow-600 dark:text-yellow-500">
                    Tanda tangan ayah sudah tersimpan. Tanda tangan baru akan menggantikan yang lama.</span>');
                }
                return null;
            });
    }

    protected function getTandaTanganIbuFormComponent(): SignaturePad
    {
        $user = $this->getFormModel();
        return SignaturePad::make('detailUser.keluarga.ttd_ibu')
            ->label('Tanda Tangan Ibu')
            ->required(false)
            ->helperText(function () use ($user) {
                if ($user->detailUser?->keluarga?->ttd_ibu) {
                    return new HtmlString('<span class="text-yellow-600 dark:text-yellow-500">
                    Tanda tangan ibu sudah tersimpan. Tanda tangan baru akan menggantikan yang lama.</span>');
                }
                return null;
            });
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
        if (isset($data['tanda_tangan']) && strpos($data['tanda_tangan'], 'data:image/png;base64,') !== false) {
            $image = $data['tanda_tangan'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10).'.'.'png';
            File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
            $data['tanda_tangan'] = 'storage/signatures/' . $imageName;
        }

        // Hanya proses tanda tangan keluarga jika bukan admin/paroki/ketua
        if (!$isAdminOrParokiOrKetua && isset($data['detailUser']['keluarga'])) {
            // Handle tanda tangan ayah
            if (isset($data['detailUser']['keluarga']['ttd_ayah']) && 
                strpos($data['detailUser']['keluarga']['ttd_ayah'], 'data:image/png;base64,') !== false) {
                $image = $data['detailUser']['keluarga']['ttd_ayah'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'ayah_'.Str::random(10).'.'.'png';
                File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
                $data['detailUser']['keluarga']['ttd_ayah'] = 'storage/signatures/' . $imageName;
            }

            // Handle tanda tangan ibu
            if (isset($data['detailUser']['keluarga']['ttd_ibu']) && 
                strpos($data['detailUser']['keluarga']['ttd_ibu'], 'data:image/png;base64,') !== false) {
                $image = $data['detailUser']['keluarga']['ttd_ibu'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'ibu_'.Str::random(10).'.'.'png';
                File::put(public_path('storage/signatures/' . $imageName), base64_decode($image));
                $data['detailUser']['keluarga']['ttd_ibu'] = 'storage/signatures/' . $imageName;
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

        // Update password jika diisi
        if (isset($data['password']) && $data['password']) {
            $record->update([
                'password' => bcrypt($data['password']),
            ]);
        }

        // Hanya update detail user jika bukan admin/paroki
        if (!$isAdminOrParoki && isset($data['detailUser'])) {
            $detailUserData = [
                'nama_baptis' => $data['detailUser']['nama_baptis'] ?? null,
                'tempat_baptis' => $data['detailUser']['tempat_baptis'] ?? null,
                'tgl_baptis' => $data['detailUser']['tgl_baptis'] ?? null,
                'no_baptis' => $data['detailUser']['no_baptis'] ?? null,
                'alamat' => $data['detailUser']['alamat'] ?? null,
                'lingkungan_id' => $data['detailUser']['lingkungan_id'] ?? null,
            ];

            $detailUser = $record->detailUser()->updateOrCreate(
                ['user_id' => $record->id],
                $detailUserData
            );

            // Handle keluarga data hanya jika bukan admin/paroki/ketua
            if (!$isAdminOrParokiOrKetua && isset($data['detailUser']['keluarga'])) {
                $keluargaData = [
                    'nama_ayah' => $data['detailUser']['keluarga']['nama_ayah'] ?? null,
                    'agama_ayah' => $data['detailUser']['keluarga']['agama_ayah'] ?? null,
                    'pekerjaan_ayah' => $data['detailUser']['keluarga']['pekerjaan_ayah'] ?? null,
                    'alamat_ayah' => $data['detailUser']['keluarga']['alamat_ayah'] ?? null,
                    'nama_ibu' => $data['detailUser']['keluarga']['nama_ibu'] ?? null,
                    'agama_ibu' => $data['detailUser']['keluarga']['agama_ibu'] ?? null,
                    'pekerjaan_ibu' => $data['detailUser']['keluarga']['pekerjaan_ibu'] ?? null,
                    'alamat_ibu' => $data['detailUser']['keluarga']['alamat_ibu'] ?? null,
                    'ttd_ayah' => $data['detailUser']['keluarga']['ttd_ayah'] ?? null,
                    'ttd_ibu' => $data['detailUser']['keluarga']['ttd_ibu'] ?? null,
                ];

                // Cek apakah keluarga sudah ada
                if ($detailUser->keluarga) {
                    // Update keluarga yang sudah ada
                    $detailUser->keluarga()->update($keluargaData);
                } else {
                    // Buat keluarga baru dan hubungkan ke detail_user
                    $keluarga = Keluarga::create($keluargaData);
                    $detailUser->update(['keluarga_id' => $keluarga->id]);
                }
            }
        }

        return $record;
    }
}