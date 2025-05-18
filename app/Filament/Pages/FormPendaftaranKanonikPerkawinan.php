<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat;
use App\Models\Keluarga;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\DetailUser;
use App\Models\Lingkungan;
use Illuminate\Support\Str;
use App\Models\CalonPasangan;
use App\Models\KetuaLingkungan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use App\Models\PendaftaranKanonikPerkawinan;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranKanonikPerkawinan extends Page implements HasForms
{    
    use InteractsWithForms;
    use HasPageShield;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';
    protected static ?string $navigationLabel = 'Pendaftaran Kanonik Perkawinan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.form-pendaftaran-kanonik-perkawinan';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $detailUser = DetailUser::where('user_id', $user->id)->first();
        $keluarga = $detailUser->keluarga ?? null;
        
        $initialData = [];
        
        if ($user->jenis_kelamin === 'Wanita') {
            // Auto-fill data calon istri
            $initialData = [
                'nama_istri' => $user->name,
                'akun_email_istri' => $user->email,
                'tempat_lahir_istri' => $user->tempat_lahir,
                'tgl_lahir_istri' => $user->tgl_lahir,
                'telepon_istri' => $user->telepon,
                'alamat_sekarang_istri' => $detailUser->alamat ?? '',
                'lingkungan_istri_id' => $detailUser->lingkungan_id ?? null,
            ];
            
            if ($keluarga) {
                $initialData += [
                    'nama_ayah_istri' => $keluarga->nama_ayah,
                    'agama_ayah_istri' => $keluarga->agama_ayah,
                    'pekerjaan_ayah_istri' => $keluarga->pekerjaan_ayah,
                    'alamat_ayah_istri' => $keluarga->alamat_ayah,
                    'nama_ibu_istri' => $keluarga->nama_ibu,
                    'agama_ibu_istri' => $keluarga->agama_ibu,
                    'pekerjaan_ibu_istri' => $keluarga->pekerjaan_ibu,
                    'alamat_ibu_istri' => $keluarga->alamat_ibu,
                ];
            }
            
            if ($detailUser) {
                $initialData += [
                    'tempat_baptis_istri' => $detailUser->tempat_baptis,
                    'tgl_baptis_istri' => $detailUser->tgl_baptis,
                ];
            }
            
            // Get lingkungan data if exists
            if ($detailUser && $detailUser->lingkungan) {
                $lingkungan = $detailUser->lingkungan;
                $ketuaLingkungan = KetuaLingkungan::with('user')
                    ->where('lingkungan_id', $lingkungan->id)
                    ->where('aktif', true)
                    ->first();
                
                $initialData += [
                    'nama_lingkungan_istri' => $lingkungan->nama_lingkungan,
                    'wilayah_istri' => $lingkungan->wilayah,
                    'paroki_istri' => $lingkungan->paroki,
                    'nama_ketua_istri' => $ketuaLingkungan ? $ketuaLingkungan->user->name : '',
                ];
            }
        } else {
            // Auto-fill data calon suami
            $initialData = [
                'nama_suami' => $user->name,
                'akun_email_suami' => $user->email,
                'tempat_lahir_suami' => $user->tempat_lahir,
                'tgl_lahir_suami' => $user->tgl_lahir,
                'telepon_suami' => $user->telepon,
                'alamat_sekarang_suami' => $detailUser->alamat ?? '',
                'lingkungan_suami_id' => $detailUser->lingkungan_id ?? null,
            ];
            
            if ($keluarga) {
                $initialData += [
                    'nama_ayah_suami' => $keluarga->nama_ayah,
                    'agama_ayah_suami' => $keluarga->agama_ayah,
                    'pekerjaan_ayah_suami' => $keluarga->pekerjaan_ayah,
                    'alamat_ayah_suami' => $keluarga->alamat_ayah,
                    'nama_ibu_suami' => $keluarga->nama_ibu,
                    'agama_ibu_suami' => $keluarga->agama_ibu,
                    'pekerjaan_ibu_suami' => $keluarga->pekerjaan_ibu,
                    'alamat_ibu_suami' => $keluarga->alamat_ibu,
                ];
            }
            
            if ($detailUser) {
                $initialData += [
                    'tempat_baptis_suami' => $detailUser->tempat_baptis,
                    'tgl_baptis_suami' => $detailUser->tgl_baptis,
                ];
            }
            
            // Get lingkungan data if exists
            if ($detailUser && $detailUser->lingkungan) {
                $lingkungan = $detailUser->lingkungan;
                $ketuaLingkungan = KetuaLingkungan::with('user')
                    ->where('lingkungan_id', $lingkungan->id)
                    ->where('aktif', true)
                    ->first();
                
                $initialData += [
                    'nama_lingkungan_suami' => $lingkungan->nama_lingkungan,
                    'wilayah_suami' => $lingkungan->wilayah,
                    'paroki_suami' => $lingkungan->paroki,
                    'nama_ketua_suami' => $ketuaLingkungan ? $ketuaLingkungan->user->name : '',
                ];
            }
        }
        
        $initialData['tgl_surat'] = now();
        
        $this->form->fill($initialData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Calon Istri')
                    ->schema([
                        TextInput::make('nama_istri')
                            ->required()
                            ->label('Nama lengkap Calon Istri')
                            ->maxLength(255),
                        TextInput::make('akun_email_istri')
                            ->required()
                            ->label('Akun Email Calon Istri')
                            ->maxLength(255),
                        TextInput::make('tempat_lahir_istri')
                            ->required()
                            ->label('Tempat Lahir Calon Istri')
                            ->maxLength(255),
                        DatePicker::make('tgl_lahir_istri')
                            ->required()
                            ->label('Tanggal Lahir Calon Istri'),
                        Textarea::make('alamat_sekarang_istri')
                            ->required()
                            ->label('Alamat Calon Istri')
                            ->columnSpanFull(),
                        Textarea::make('alamat_setelah_menikah_istri')
                            ->required()
                            ->label('Alamat Calon Istri Setelah Menikah')
                            ->columnSpanFull(),
                        TextInput::make('telepon_istri')
                            ->tel()
                            ->required()
                            ->label('Telepon Calon Istri')
                            ->maxLength(255),
                        TextInput::make('pekerjaan_istri')
                            ->required()
                            ->label('Pekerjaan Calon Istri')
                            ->maxLength(255),
                        Select::make('pendidikan_terakhir_istri')
                            ->required()
                            ->label('Pendidikan Terakhir Calon Istri')
                            ->options([
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                                'SMA' => 'SMA',
                            ]),
                        Select::make('agama_istri')
                            ->required()
                            ->label('Agama Calon Istri')
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                            ]),
                        TextInput::make('tempat_baptis_istri')
                            ->maxLength(255),
                        DatePicker::make('tgl_baptis_istri')
                            ->label('Tanggal Baptis Calon Istri'),
                        SignaturePad::make('ttd_calon_istri')
                            ->required()
                            ->label('Tanda Tangan Calon Istri'),

                        Fieldset::make('Data Orang Tua Calon Istri')
                            ->schema([
                                TextInput::make('nama_ayah_istri')
                                    ->required()
                                    ->label('Nama Ayah Calon Istri')
                                    ->maxLength(255),
                                Select::make('agama_ayah_istri')
                                    ->required()
                                    ->label('Agama Ayah Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                TextInput::make('pekerjaan_ayah_istri')
                                    ->required()
                                    ->label('Pekerjaan Ayah Calon Istri')
                                    ->maxLength(255),
                                Textarea::make('alamat_ayah_istri')
                                    ->required()
                                    ->label('Alamat Ayah Calon Istri')
                                    ->columnSpanFull(),
                                TextInput::make('nama_ibu_istri')
                                    ->required()
                                    ->label('Nama Ibu Calon Istri')
                                    ->maxLength(255),
                                Select::make('agama_ibu_istri')
                                    ->required()
                                    ->label('Agama Ibu Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                TextInput::make('pekerjaan_ibu_istri')
                                    ->required()
                                    ->label('Pekerjaan Ibu Calon Istri')
                                    ->maxLength(255),
                                Textarea::make('alamat_ibu_istri')
                                    ->required()
                                    ->label('Alamat Ibu Calon Istri')
                                    ->columnSpanFull(),
                            ]),   
                            Fieldset::make('Data Lingkungan Calon Istri')
                                ->schema([
                                    Select::make('lingkungan_istri_id')
                                            ->label('Nama Lingkungan/Stasi Calon Istri')
                                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lingkungan = Lingkungan::find($state);
                                                    $ketuaLingkungan = KetuaLingkungan::with('user')
                                                        ->where('lingkungan_id', $state)
                                                        ->where('aktif', true)
                                                        ->first();
                                                    if ($lingkungan) {
                                                        $set('nama_lingkungan_istri', $lingkungan->nama_lingkungan);
                                                        $set('wilayah_istri', $lingkungan->wilayah);
                                                        $set('paroki_istri', $lingkungan->paroki);
                                                    }
                                                    if ($ketuaLingkungan) {
                                                        $set('nama_ketua_istri', $ketuaLingkungan->user->name);
                                                    }
                                                }
                                            }),
                                    TextInput::make('nama_lingkungan_istri')
                                        ->label('Nama Lingkungan (Jika tidak ada di pilihan)')
                                        ->maxLength(255),
                                    TextInput::make('wilayah_istri')
                                        ->label('Wilayah')
                                        ->maxLength(255),
                                    TextInput::make('paroki_istri')
                                        ->label('Paroki')
                                        ->maxLength(255),
                                    TextInput::make('nama_ketua_istri')
                                        ->required()
                                        ->label('Nama Ketua Lingkungan Calon Istri')
                                        ->maxLength(255),
                                    SignaturePad::make('ttd_ketua_istri')
                                        ->label('Tanda Tangan Ketua Lingkungan Calon Istri'),
                                ])                          
                    ]),
                    Fieldset::make('Data Calon Suami')
                        ->schema([
                            TextInput::make('nama_suami')
                                ->required()
                                ->label('Nama Calon Suami')
                                ->maxLength(255),
                            TextInput::make('akun_email_suami')
                                ->required()
                                ->label('Akun Email Calon Suami')
                                ->maxLength(255),
                            TextInput::make('tempat_lahir_suami')
                                ->required()
                                ->label('Tempat Lahir Calon Suami')
                                ->maxLength(255),
                            DatePicker::make('tgl_lahir_suami')
                                ->required()
                                ->label('Tanggal Lahir Calon Suami'),
                            Textarea::make('alamat_sekarang_suami')
                                ->required()
                                ->label('Alamat Sekarang Calon Suami')
                                ->columnSpanFull(),
                            Textarea::make('alamat_setelah_menikah_suami')
                                ->required()
                                ->label('Alamat Setelah Menikah Calon Suami')
                                ->columnSpanFull(),
                            TextInput::make('telepon_suami')
                                ->tel()
                                ->required()
                                ->label('Telepon Calon Suami')
                                ->maxLength(255),
                            TextInput::make('pekerjaan_suami')
                                ->required()
                                ->label('Pekerjaan Calon Suami')
                                ->maxLength(255),
                            Select::make('pendidikan_terakhir_suami')
                                ->required()
                                ->label('Pendidikan Terakhir Calon Suami')
                                ->options([
                                    'Diploma/Sarjana' => 'Diploma/Sarjana',
                                    'SMA' => 'SMA',
                                ]),
                            Select::make('agama_suami')
                                ->required()
                                ->label('Agama Calon Suami')
                                ->options([
                                    'Katolik' => 'Katolik',
                                    'Protestan' => 'Protestan',
                                    'Islam' => 'Islam',
                                    'Hindu' => 'Hindu',
                                    'Budha' => 'Budha',
                                ]),
                            TextInput::make('tempat_baptis_suami')
                                ->maxLength(255)
                                ->label('Tempat Baptis Calon Suami'),
                            DatePicker::make('tgl_baptis_suami')
                                ->label('Tanggal Baptis Calon Suami'),
                            SignaturePad::make('ttd_calon_suami')
                                ->required()
                                ->label('Tanda Tangan Calon Suami'),
                            Fieldset::make('Data Orang Tua Calon Suami')
                                ->schema([
                                    TextInput::make('nama_ayah_suami')
                                        ->required()
                                        ->label('Nama Ayah Calon Suami')
                                        ->maxLength(255),
                                    Select::make('agama_ayah_suami')
                                        ->required()
                                        ->label('Agama Ayah Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    TextInput::make('pekerjaan_ayah_suami')
                                        ->required()
                                        ->label('Pekerjaan Ayah Calon Suami')
                                        ->maxLength(255),
                                    Textarea::make('alamat_ayah_suami')
                                        ->required()
                                        ->label('Alamat Ayah Calon Suami')
                                        ->columnSpanFull(),
                                    TextInput::make('nama_ibu_suami')
                                        ->required()
                                        ->label('Nama Ibu Calon Suami')
                                        ->maxLength(255),
                                    Select::make('agama_ibu_suami')
                                        ->required()
                                        ->label('Agama Ibu Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    TextInput::make('pekerjaan_ibu_suami')
                                        ->required()
                                        ->label('Pekerjaan Ibu Calon Suami')
                                        ->maxLength(255),
                                    Textarea::make('alamat_ibu_suami')
                                        ->required()
                                        ->label('Alamat Ibu Calon Suami')
                                        ->columnSpanFull(),
                                ]),
                                Fieldset::make('Data Lingkungan Calon Suami')
                                    ->schema([
                                        Select::make('lingkungan_suami_id')
                                            ->label('Nama Lingkungan/Stasi Calon Suami')
                                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lingkungan = Lingkungan::find($state);
                                                    $ketuaLingkungan = KetuaLingkungan::with('user')
                                                        ->where('lingkungan_id', $state)
                                                        ->where('aktif', true)
                                                        ->first();
                                                    if ($lingkungan) {
                                                        $set('nama_lingkungan_suami', $lingkungan->nama_lingkungan);
                                                        $set('wilayah_suami', $lingkungan->wilayah);
                                                        $set('paroki_suami', $lingkungan->paroki);
                                                    }
                                                    if ($ketuaLingkungan) {
                                                        $set('nama_ketua_suami', $ketuaLingkungan->user->name);
                                                    }
                                                }
                                            }),
                                        TextInput::make('nama_lingkungan_suami')
                                            ->required()
                                            ->label('Nama Lingkungan (Jika tidak ada di pilihan)')
                                            ->maxLength(255),
                                        TextInput::make('wilayah_suami')
                                            ->required()
                                            ->label('Wilayah')
                                            ->maxLength(255),
                                        TextInput::make('paroki_suami')
                                            ->required()
                                            ->label('Paroki')
                                            ->maxLength(255),
                                        TextInput::make('nama_ketua_suami')
                                            ->required()
                                            ->label('Nama Ketua Lingkungan Calon Suami')
                                            ->maxLength(255),
                                        SignaturePad::make('ttd_ketua_suami')
                                            ->label('Tanda Tangan Ketua Lingkungan Calon Suami'),
                                    ])
                        ]),
                        Fieldset::make('Data Perkawinan')
                            ->schema([
                                TextInput::make('lokasi_gereja')
                                    ->required()
                                    ->label('Lokasi Gereja')
                                    ->maxLength(255),
                                DatePicker::make('tgl_pernikahan')
                                    ->required()
                                    ->label('Tanggal Pernikahan'),
                                TimePicker::make('waktu_pernikahan')
                                    ->required()
                                    ->label('Waktu Pernikahan'),
                                DatePicker::make('tgl_surat')
                                    ->required()
                                    ->label('Tanggal Surat')
                                    ->default(now())
                                    ->readOnly(),
                                Hidden::make('nomor_surat'),
                                Hidden::make('nama_pastor'),
                                Hidden::make('ttd_pastor'),
                            ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        
        // Proses tanda tangan
        $tandaTanganFields = [
            'ttd_calon_istri',
            'ttd_calon_suami',
            'ttd_ketua_istri',
            'ttd_ketua_suami'
        ];

        foreach ($tandaTanganFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $image = $data[$field];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.png';
                File::put(storage_path(). '/' . $imageName, base64_decode($image));
                $data[$field] = $imageName;
            }
        }

        $user = Auth::user();
        
        if ($user->jenis_kelamin === 'Wanita') {
            // Buat user baru untuk calon suami (karena yang login wanita)
            $userSuami = User::create([
                'name' => $this->data['nama_suami'],
                'email' => $this->data['akun_email_suami'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => 'Pria',
                'tempat_lahir' => $this->data['tempat_lahir_suami'],
                'tgl_lahir' => $this->data['tgl_lahir_suami'],
                'telepon' => $this->data['telepon_suami'],
            ]);

            // Buat keluarga baru untuk calon suami
            $keluargaSuami = Keluarga::create([
                'nama_ayah' => $this->data['nama_ayah_suami'],
                'agama_ayah' => $this->data['agama_ayah_suami'],
                'pekerjaan_ayah' => $this->data['pekerjaan_ayah_suami'],
                'alamat_ayah' => $this->data['alamat_ayah_suami'],
                'nama_ibu' => $this->data['nama_ibu_suami'],
                'agama_ibu' => $this->data['agama_ibu_suami'],
                'pekerjaan_ibu' => $this->data['pekerjaan_ibu_suami'],
                'alamat_ibu' => $this->data['alamat_ibu_suami'],
            ]);

            // Buat detail user untuk calon suami
            $detailUserSuami = DetailUser::create([
                'user_id' => $userSuami->id,
                'lingkungan_id' => $this->data['lingkungan_suami_id'],
                'keluarga_id' => $keluargaSuami->id,
                'alamat' => $this->data['alamat_sekarang_suami'],
                'tempat_baptis' => $this->data['tempat_baptis_suami'] ?? null,
                'tgl_baptis' => $this->data['tgl_baptis_suami'] ?? null,
            ]);

            // Buat calon pasangan untuk suami
            $calonSuami = CalonPasangan::create([
                'user_id' => $userSuami->id,
                'lingkungan_id' => $this->data['lingkungan_suami_id'],
                'ketua_lingkungan_id' => $this->data['ketua_lingkungan_suami_id'] ?? null,
                'nama_lingkungan' => $this->data['nama_lingkungan_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->nama_lingkungan ?? null,
                'nama_ketua' => $this->data['nama_ketua_suami'],
                'wilayah' => $this->data['wilayah_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->wilayah ?? null,
                'paroki' => $this->data['paroki_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->paroki ?? null,
                'keluarga_id' => $keluargaSuami->id,
                'alamat_stlh_menikah' => $this->data['alamat_setelah_menikah_suami'],
                'pekerjaan' => $this->data['pekerjaan_suami'],
                'pendidikan_terakhir' => $this->data['pendidikan_terakhir_suami'],
                'agama' => $this->data['agama_suami'],
                'jenis_kelamin' => 'Pria',
            ]);

            $data['calon_suami_id'] = $calonSuami->id;
            
            // Untuk calon istri (yang login), gunakan data yang sudah ada
            $detailUser = DetailUser::where('user_id', $user->id)->first();
            
            // Buat calon pasangan untuk istri
            $calonIstri = CalonPasangan::create([
                'user_id' => $user->id,
                'lingkungan_id' => $this->data['lingkungan_istri_id'],
                'ketua_lingkungan_id' => $this->data['ketua_lingkungan_istri_id'] ?? null,
                'nama_lingkungan' => $this->data['nama_lingkungan_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->nama_lingkungan ?? null,
                'nama_ketua' => $this->data['nama_ketua_istri'],
                'wilayah' => $this->data['wilayah_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->wilayah ?? null,
                'paroki' => $this->data['paroki_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->paroki ?? null,
                'keluarga_id' => $detailUser->keluarga_id,
                'alamat_stlh_menikah' => $this->data['alamat_setelah_menikah_istri'],
                'pekerjaan' => $this->data['pekerjaan_istri'],
                'pendidikan_terakhir' => $this->data['pendidikan_terakhir_istri'],
                'agama' => $this->data['agama_istri'],
                'jenis_kelamin' => 'Wanita',
            ]);

            $data['calon_istri_id'] = $calonIstri->id;
        } else {
            // Buat user baru untuk calon istri (karena yang login pria)
            $userIstri = User::create([
                'name' => $this->data['nama_istri'],
                'email' => $this->data['akun_email_istri'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => 'Wanita',
                'tempat_lahir' => $this->data['tempat_lahir_istri'],
                'tgl_lahir' => $this->data['tgl_lahir_istri'],
                'telepon' => $this->data['telepon_istri'],
            ]);

            // Buat keluarga baru untuk calon istri
            $keluargaIstri = Keluarga::create([
                'nama_ayah' => $this->data['nama_ayah_istri'],
                'agama_ayah' => $this->data['agama_ayah_istri'],
                'pekerjaan_ayah' => $this->data['pekerjaan_ayah_istri'],
                'alamat_ayah' => $this->data['alamat_ayah_istri'],
                'nama_ibu' => $this->data['nama_ibu_istri'],
                'agama_ibu' => $this->data['agama_ibu_istri'],
                'pekerjaan_ibu' => $this->data['pekerjaan_ibu_istri'],
                'alamat_ibu' => $this->data['alamat_ibu_istri'],
            ]);

            // Buat detail user untuk calon istri
            $detailUserIstri = DetailUser::create([
                'user_id' => $userIstri->id,
                'lingkungan_id' => $this->data['lingkungan_istri_id'],
                'keluarga_id' => $keluargaIstri->id,
                'alamat' => $this->data['alamat_sekarang_istri'],
                'tempat_baptis' => $this->data['tempat_baptis_istri'] ?? null,
                'tgl_baptis' => $this->data['tgl_baptis_istri'] ?? null,
            ]);

            // Buat calon pasangan untuk istri
            $calonIstri = CalonPasangan::create([
                'user_id' => $userIstri->id,
                'lingkungan_id' => $this->data['lingkungan_istri_id'],
                'ketua_lingkungan_id' => $this->data['ketua_lingkungan_istri_id'] ?? null,
                'nama_lingkungan' => $this->data['nama_lingkungan_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->nama_lingkungan ?? null,
                'nama_ketua' => $this->data['nama_ketua_istri'],
                'wilayah' => $this->data['wilayah_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->wilayah ?? null,
                'paroki' => $this->data['paroki_istri'] ?? Lingkungan::find($this->data['lingkungan_istri_id'])->paroki ?? null,
                'keluarga_id' => $keluargaIstri->id,
                'alamat_stlh_menikah' => $this->data['alamat_setelah_menikah_istri'],
                'pekerjaan' => $this->data['pekerjaan_istri'],
                'pendidikan_terakhir' => $this->data['pendidikan_terakhir_istri'],
                'agama' => $this->data['agama_istri'],
                'jenis_kelamin' => 'Wanita',
            ]);

            $data['calon_istri_id'] = $calonIstri->id;
            
            // Untuk calon suami (yang login), gunakan data yang sudah ada
            $detailUser = DetailUser::where('user_id', $user->id)->first();
            
            // Buat calon pasangan untuk suami
            $calonSuami = CalonPasangan::create([
                'user_id' => $user->id,
                'lingkungan_id' => $this->data['lingkungan_suami_id'],
                'ketua_lingkungan_id' => $this->data['ketua_lingkungan_suami_id'] ?? null,
                'nama_lingkungan' => $this->data['nama_lingkungan_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->nama_lingkungan ?? null,
                'nama_ketua' => $this->data['nama_ketua_suami'],
                'wilayah' => $this->data['wilayah_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->wilayah ?? null,
                'paroki' => $this->data['paroki_suami'] ?? Lingkungan::find($this->data['lingkungan_suami_id'])->paroki ?? null,
                'keluarga_id' => $detailUser->keluarga_id,
                'alamat_stlh_menikah' => $this->data['alamat_setelah_menikah_suami'],
                'pekerjaan' => $this->data['pekerjaan_suami'],
                'pendidikan_terakhir' => $this->data['pendidikan_terakhir_suami'],
                'agama' => $this->data['agama_suami'],
                'jenis_kelamin' => 'Pria',
            ]);

            $data['calon_suami_id'] = $calonSuami->id;
        }
        
        $pendaftaranKanonik = PendaftaranKanonikPerkawinan::create($data);
        
        $surat = Surat::create([
            'user_id' => Auth::id(),
            'lingkungan_id' => Auth::user()->detailUser->lingkungan_id ?? null,
            'jenis_surat' => 'pendaftaran_perkawinan',
            'perihal' => 'Pendaftaran Kanonik Perkawinan',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu',
        ]);
        
        if ($surat) {
            $pendaftaranKanonik->update(['surat_id' => $surat->id]);
        }

        Notification::make()
            ->title('Pengajuan Pendaftaran Kanonik Perkawinan berhasil dibuat')
            ->success()
            ->send();
            
        $this->form->fill();
    }
}