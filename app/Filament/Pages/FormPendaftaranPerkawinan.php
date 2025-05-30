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
use App\Models\PendaftaranPerkawinan;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranPerkawinan extends Page implements HasForms
{    
    use InteractsWithForms;
    use HasPageShield;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';
    protected static ?string $navigationLabel = 'Pendaftaran Perkawinan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.form-pendaftaran-perkawinan';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $detailUser = DetailUser::where('user_id', $user->id)->first();
        
        // Jika detail user belum ada, buat baru
        if (!$detailUser) {
            $detailUser = DetailUser::create(['user_id' => $user->id]);
        }
        
        // Jika user belum memiliki keluarga, buat keluarga kosong
        if (!$detailUser->keluarga) {
            $keluarga = Keluarga::create([]);
            $detailUser->update(['keluarga_id' => $keluarga->id]);
        } else {
            $keluarga = $detailUser->keluarga;
        }
        
        $initialData = [];
        
        if ($user->jenis_kelamin === 'Wanita') {
            // Auto-fill data calon istri
            $initialData = [
                'nama_istri' => $user->name,
                'akun_email_istri' => $user->email,
                'tempat_lahir_istri' => $user->tempat_lahir ?? '',
                'tgl_lahir_istri' => $user->tgl_lahir ?? '',
                'telepon_istri' => $user->telepon ?? '',
                'alamat_sekarang_istri' => $detailUser->alamat ?? '',
                'lingkungan_istri_id' => $detailUser->lingkungan_id ?? null,
            ];
            
            // Data keluarga
            $initialData += [
                'nama_ayah_istri' => $keluarga->nama_ayah ?? '',
                'agama_ayah_istri' => $keluarga->agama_ayah ?? '',
                'pekerjaan_ayah_istri' => $keluarga->pekerjaan_ayah ?? '',
                'alamat_ayah_istri' => $keluarga->alamat_ayah ?? '',
                'nama_ibu_istri' => $keluarga->nama_ibu ?? '',
                'agama_ibu_istri' => $keluarga->agama_ibu ?? '',
                'pekerjaan_ibu_istri' => $keluarga->pekerjaan_ibu ?? '',
                'alamat_ibu_istri' => $keluarga->alamat_ibu ?? '',
            ];
            
            // Data baptis
            $initialData += [
                'tempat_baptis_istri' => $detailUser->tempat_baptis ?? '',
                'tgl_baptis_istri' => $detailUser->tgl_baptis ?? '',
            ];
            
            // Data lingkungan jika ada
            if ($detailUser->lingkungan) {
                $lingkungan = $detailUser->lingkungan;
                $ketuaLingkungan = KetuaLingkungan::with('user')
                    ->where('lingkungan_id', $lingkungan->id)
                    ->where('aktif', true)
                    ->first();
                
                $initialData += [
                    'nama_lingkungan_istri' => $lingkungan->nama_lingkungan ?? '',
                    'wilayah_istri' => $lingkungan->wilayah ?? '',
                    'paroki_istri' => $lingkungan->paroki ?? '',
                    'nama_ketua_istri' => $ketuaLingkungan ? $ketuaLingkungan->user->name : '',
                ];
            }
        } else {
            // Auto-fill data calon suami
            $initialData = [
                'nama_suami' => $user->name,
                'akun_email_suami' => $user->email,
                'tempat_lahir_suami' => $user->tempat_lahir ?? '',
                'tgl_lahir_suami' => $user->tgl_lahir ?? '',
                'telepon_suami' => $user->telepon ?? '',
                'alamat_sekarang_suami' => $detailUser->alamat ?? '',
                'lingkungan_suami_id' => $detailUser->lingkungan_id ?? null,
            ];
            
            // Data keluarga
            $initialData += [
                'nama_ayah_suami' => $keluarga->nama_ayah ?? '',
                'agama_ayah_suami' => $keluarga->agama_ayah ?? '',
                'pekerjaan_ayah_suami' => $keluarga->pekerjaan_ayah ?? '',
                'alamat_ayah_suami' => $keluarga->alamat_ayah ?? '',
                'nama_ibu_suami' => $keluarga->nama_ibu ?? '',
                'agama_ibu_suami' => $keluarga->agama_ibu ?? '',
                'pekerjaan_ibu_suami' => $keluarga->pekerjaan_ibu ?? '',
                'alamat_ibu_suami' => $keluarga->alamat_ibu ?? '',
            ];
            
            // Data baptis
            $initialData += [
                'tempat_baptis_suami' => $detailUser->tempat_baptis ?? '',
                'tgl_baptis_suami' => $detailUser->tgl_baptis ?? '',
            ];
            
            // Data lingkungan jika ada
            if ($detailUser->lingkungan) {
                $lingkungan = $detailUser->lingkungan;
                $ketuaLingkungan = KetuaLingkungan::with('user')
                    ->where('lingkungan_id', $lingkungan->id)
                    ->where('aktif', true)
                    ->first();
                
                $initialData += [
                    'nama_lingkungan_suami' => $lingkungan->nama_lingkungan ?? '',
                    'wilayah_suami' => $lingkungan->wilayah ?? '',
                    'paroki_suami' => $lingkungan->paroki ?? '',
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
                                Select::make('lokasi_gereja')
                                    ->required()
                                    ->label('Lokasi Gereja')
                                    ->options([
                                        'St. Stephanus Cilacap' => 'Gereja St. Stephanus Cilacap',
                                        'St. Eugenius De Mazenod Cilacap' => 'Kapel St. Eugenius De Mazenod Cilacap'
                                    ]),
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

        /** @var User $user */
        $user = Auth::user();
        $detailUser = DetailUser::where('user_id', $user->id)->first();
        
        // Update data user yang login jika ada perubahan
        $user->update([
            'tempat_lahir' => $user->jenis_kelamin === 'Wanita' ? $data['tempat_lahir_istri'] : $data['tempat_lahir_suami'],
            'tgl_lahir' => $user->jenis_kelamin === 'Wanita' ? $data['tgl_lahir_istri'] : $data['tgl_lahir_suami'],
            'telepon' => $user->jenis_kelamin === 'Wanita' ? $data['telepon_istri'] : $data['telepon_suami'],
        ]);
        
        // Update detail user yang login
        $detailUser->update([
            'alamat' => $user->jenis_kelamin === 'Wanita' ? $data['alamat_sekarang_istri'] : $data['alamat_sekarang_suami'],
            'tempat_baptis' => $user->jenis_kelamin === 'Wanita' ? $data['tempat_baptis_istri'] : $data['tempat_baptis_suami'],
            'tgl_baptis' => $user->jenis_kelamin === 'Wanita' ? $data['tgl_baptis_istri'] : $data['tgl_baptis_suami'],
            'lingkungan_id' => $user->jenis_kelamin === 'Wanita' ? $data['lingkungan_istri_id'] : $data['lingkungan_suami_id'],
        ]);
        
        // Update data keluarga user yang login
        if ($detailUser->keluarga) {
            $detailUser->keluarga->update([
                'nama_ayah' => $user->jenis_kelamin === 'Wanita' ? $data['nama_ayah_istri'] : $data['nama_ayah_suami'],
                'agama_ayah' => $user->jenis_kelamin === 'Wanita' ? $data['agama_ayah_istri'] : $data['agama_ayah_suami'],
                'pekerjaan_ayah' => $user->jenis_kelamin === 'Wanita' ? $data['pekerjaan_ayah_istri'] : $data['pekerjaan_ayah_suami'],
                'alamat_ayah' => $user->jenis_kelamin === 'Wanita' ? $data['alamat_ayah_istri'] : $data['alamat_ayah_suami'],
                'nama_ibu' => $user->jenis_kelamin === 'Wanita' ? $data['nama_ibu_istri'] : $data['nama_ibu_suami'],
                'agama_ibu' => $user->jenis_kelamin === 'Wanita' ? $data['agama_ibu_istri'] : $data['agama_ibu_suami'],
                'pekerjaan_ibu' => $user->jenis_kelamin === 'Wanita' ? $data['pekerjaan_ibu_istri'] : $data['pekerjaan_ibu_suami'],
                'alamat_ibu' => $user->jenis_kelamin === 'Wanita' ? $data['alamat_ibu_istri'] : $data['alamat_ibu_suami'],
            ]);
        }
        
        if ($user->jenis_kelamin === 'Wanita') {
            // Buat user baru untuk calon suami (karena yang login wanita)
            $userSuami = User::create([
                'name' => $data['nama_suami'],
                'email' => $data['akun_email_suami'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => 'Pria',
                'tempat_lahir' => $data['tempat_lahir_suami'],
                'tgl_lahir' => $data['tgl_lahir_suami'],
                'telepon' => $data['telepon_suami'],
            ]);

            // Assign role 'umat' untuk calon suami
            $userSuami->assignRole('umat');

            // Buat keluarga baru untuk calon suami
            $keluargaSuami = Keluarga::create([
                'nama_ayah' => $data['nama_ayah_suami'],
                'agama_ayah' => $data['agama_ayah_suami'],
                'pekerjaan_ayah' => $data['pekerjaan_ayah_suami'],
                'alamat_ayah' => $data['alamat_ayah_suami'],
                'nama_ibu' => $data['nama_ibu_suami'],
                'agama_ibu' => $data['agama_ibu_suami'],
                'pekerjaan_ibu' => $data['pekerjaan_ibu_suami'],
                'alamat_ibu' => $data['alamat_ibu_suami'],
            ]);

            // Buat detail user untuk calon suami
            $detailUserSuami = DetailUser::create([
                'user_id' => $userSuami->id,
                'lingkungan_id' => $data['lingkungan_suami_id'],
                'keluarga_id' => $keluargaSuami->id,
                'alamat' => $data['alamat_sekarang_suami'],
                'tempat_baptis' => $data['tempat_baptis_suami'] ?? null,
                'tgl_baptis' => $data['tgl_baptis_suami'] ?? null,
            ]);

            // Untuk calon suami
            $lingkunganSuami = Lingkungan::find($data['lingkungan_suami_id']);
            $ketuaLingkunganSuami = $lingkunganSuami
                ? $lingkunganSuami->ketuaLingkungans()->where('aktif', true)->first()
                : null;

            $calonPasanganCwo = [
                'user_id' => $userSuami->id,
                'lingkungan_id' => $data['lingkungan_suami_id'],
                'ketua_lingkungan_id' => $ketuaLingkunganSuami ? $ketuaLingkunganSuami->id : null,
                'nama_lingkungan' => $data['nama_lingkungan_suami'] ?? ($lingkunganSuami->nama_lingkungan ?? null),
                'nama_ketua' => $data['nama_ketua_suami'],
                'wilayah' => $data['wilayah_suami'] ?? ($lingkunganSuami->wilayah ?? null),
                'paroki' => $data['paroki_suami'] ?? ($lingkunganSuami->paroki ?? null),
                'keluarga_id' => $keluargaSuami->id,
                'alamat_stlh_menikah' => $data['alamat_setelah_menikah_suami'],
                'pekerjaan' => $data['pekerjaan_suami'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir_suami'],
                'agama' => $data['agama_suami'],
                'jenis_kelamin' => 'Pria',
            ];

            // Buat calon pasangan untuk suami
            $calonSuami = CalonPasangan::create($calonPasanganCwo);

            $data['calon_suami_id'] = $calonSuami->id;
            
            // Untuk calon istri (yang login)
            $lingkunganIstri = $detailUser->lingkungan;
            $ketuaLingkunganIstri = $lingkunganIstri
                ? $lingkunganIstri->ketuaLingkungans()->where('aktif', true)->first()
                : null;

            $calonPasanganCwe = [
                'user_id' => $user->id,
                'lingkungan_id' => $detailUser->lingkungan_id,
                'ketua_lingkungan_id' => $ketuaLingkunganIstri ? $ketuaLingkunganIstri->id : null,
                'nama_lingkungan' => $lingkunganIstri->nama_lingkungan ?? null,
                'nama_ketua' => $ketuaLingkunganIstri ? $ketuaLingkunganIstri->user->name : null,
                'wilayah' => $lingkunganIstri->wilayah ?? null,
                'paroki' => $lingkunganIstri->paroki ?? null,
                'keluarga_id' => $detailUser->keluarga_id,
                'alamat_stlh_menikah' => $data['alamat_setelah_menikah_istri'],
                'pekerjaan' => $data['pekerjaan_istri'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir_istri'],
                'agama' => $data['agama_istri'],
                'jenis_kelamin' => 'Wanita',
            ];

            // Buat calon pasangan untuk istri
            $calonIstri = CalonPasangan::create($calonPasanganCwe);

            $data['calon_istri_id'] = $calonIstri->id;

        } else {
            // Buat user baru untuk calon istri (karena yang login pria)
            $userIstri = User::create([
                'name' => $data['nama_istri'],
                'email' => $data['akun_email_istri'],
                'password' => bcrypt('12345678'),
                'jenis_kelamin' => 'Wanita',
                'tempat_lahir' => $data['tempat_lahir_istri'],
                'tgl_lahir' => $data['tgl_lahir_istri'],
                'telepon' => $data['telepon_istri'],
            ]);

            // Assign role 'umat' untuk calon istri
            $userIstri->assignRole('umat');

            // Buat keluarga baru untuk calon istri
            $keluargaIstri = Keluarga::create([
                'nama_ayah' => $data['nama_ayah_istri'],
                'agama_ayah' => $data['agama_ayah_istri'],
                'pekerjaan_ayah' => $data['pekerjaan_ayah_istri'],
                'alamat_ayah' => $data['alamat_ayah_istri'],
                'nama_ibu' => $data['nama_ibu_istri'],
                'agama_ibu' => $data['agama_ibu_istri'],
                'pekerjaan_ibu' => $data['pekerjaan_ibu_istri'],
                'alamat_ibu' => $data['alamat_ibu_istri'],
            ]);

            // Buat detail user untuk calon istri
            $detailUserIstri = DetailUser::create([
                'user_id' => $userIstri->id,
                'lingkungan_id' => $data['lingkungan_istri_id'],
                'keluarga_id' => $keluargaIstri->id,
                'alamat' => $data['alamat_sekarang_istri'],
                'tempat_baptis' => $data['tempat_baptis_istri'] ?? null,
                'tgl_baptis' => $data['tgl_baptis_istri'] ?? null,
            ]);

            // Untuk calon istri
            $lingkunganIstri = Lingkungan::find($data['lingkungan_istri_id']);
            $ketuaLingkunganIstri = $lingkunganIstri
                ? $lingkunganIstri->ketuaLingkungans()->where('aktif', true)->first()
                : null;

            $calonPasanganCwe = [
                'user_id' => $userIstri->id,
                'lingkungan_id' => $data['lingkungan_istri_id'],
                'ketua_lingkungan_id' => $ketuaLingkunganIstri ? $ketuaLingkunganIstri->id : null,
                'nama_lingkungan' => $data['nama_lingkungan_istri'] ?? ($lingkunganIstri->nama_lingkungan ?? null),
                'nama_ketua' => $data['nama_ketua_istri'],
                'wilayah' => $data['wilayah_istri'] ?? ($lingkunganIstri->wilayah ?? null),
                'paroki' => $data['paroki_istri'] ?? ($lingkunganIstri->paroki ?? null),
                'keluarga_id' => $keluargaIstri->id,
                'alamat_stlh_menikah' => $data['alamat_setelah_menikah_istri'],
                'pekerjaan' => $data['pekerjaan_istri'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir_istri'],
                'agama' => $data['agama_istri'],
                'jenis_kelamin' => 'Wanita',
            ];

            // Buat calon pasangan untuk istri
            $calonIstri = CalonPasangan::create($calonPasanganCwe);

            $data['calon_istri_id'] = $calonIstri->id;
            
            // Untuk calon suami (yang login)
            $lingkunganSuami = $detailUser->lingkungan;
            $ketuaLingkunganSuami = $lingkunganSuami
                ? $lingkunganSuami->ketuaLingkungans()->where('aktif', true)->first()
                : null;

            $calonPasanganCwo = [
                'user_id' => $user->id,
                'lingkungan_id' => $detailUser->lingkungan_id,
                'ketua_lingkungan_id' => $ketuaLingkunganSuami ? $ketuaLingkunganSuami->id : null,
                'nama_lingkungan' => $lingkunganSuami->nama_lingkungan ?? null,
                'nama_ketua' => $ketuaLingkunganSuami ? $ketuaLingkunganSuami->user->name : null,
                'wilayah' => $lingkunganSuami->wilayah ?? null,
                'paroki' => $lingkunganSuami->paroki ?? null,
                'keluarga_id' => $detailUser->keluarga_id,
                'alamat_stlh_menikah' => $data['alamat_setelah_menikah_suami'],
                'pekerjaan' => $data['pekerjaan_suami'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir_suami'],
                'agama' => $data['agama_suami'],
                'jenis_kelamin' => 'Pria',
            ];

            // Buat calon pasangan untuk suami
            $calonSuami = CalonPasangan::create($calonPasanganCwo);

            $data['calon_suami_id'] = $calonSuami->id;
        }
        
        $pendaftaranKanonik = PendaftaranPerkawinan::create($data);
        
        $surat = Surat::create([
            'user_id' => Auth::id(),
            'lingkungan_id' => $detailUser->lingkungan_id ?? null,
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