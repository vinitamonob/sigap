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
use App\Models\KetuaLingkungan;
use App\Models\PendaftaranBaptis;
use Filament\Forms\Components\Radio;
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
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class FormPendaftaranBaptis extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;
    
    protected static ?string $navigationGroup = 'Form Pengajuan';
    protected static ?string $navigationLabel = 'Pendaftaran Baptis';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.form-pendaftaran-baptis';

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

        // Isi form dengan data yang ada
        $this->form->fill([
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tempat_lahir' => $user->tempat_lahir,
            'tgl_lahir' => $user->tgl_lahir,
            'telepon' => $user->telepon,
            'nama_baptis' => $detailUser->nama_baptis,
            'alamat' => $detailUser->alamat,
            'lingkungan_id' => $detailUser->lingkungan_id,
            'paroki' => $detailUser->lingkungan->paroki ?? 'St. Stephanus Cilacap',
            'nama_lingkungan' => $detailUser->lingkungan->nama_lingkungan ?? null,
            'tgl_surat' => now(),
            
            // Data keluarga jika ada
            'keluarga_id' => $keluarga->id,
            'nama_ayah' => $keluarga->nama_ayah,
            'agama_ayah' => $keluarga->agama_ayah,
            'nama_ibu' => $keluarga->nama_ibu,
            'agama_ibu' => $keluarga->agama_ibu,
            'alamat_keluarga' => $keluarga->alamat_ayah ?? $keluarga->alamat_ibu,
            'ttd_ortu' => $keluarga->ttd_ayah ?? $keluarga->ttd_ibu,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Hidden::make('nomor_surat'),
                        Hidden::make('user_id'),
                        Hidden::make('nama_lingkungan'),
                        Hidden::make('ketua_lingkungan_id'),
                        Select::make('lingkungan_id')
                            ->required()
                            ->label('Nama Lingkungan/Stasi')
                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $lingkungan = Lingkungan::find($state);
                                    $ketuaLingkungan = KetuaLingkungan::where('lingkungan_id', $state)
                                        ->where('aktif', true)
                                        ->first();
                                    
                                    if ($lingkungan) {
                                        $set('nama_lingkungan', $lingkungan->nama_lingkungan);
                                        $set('paroki', $lingkungan->paroki ?? 'St. Stephanus Cilacap');
                                    }
                                    
                                    if ($ketuaLingkungan) {
                                        $set('ketua_lingkungan_id', $ketuaLingkungan->id);
                                    }
                                }
                            }),
                        TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->readOnly(), 
                        DatePicker::make('tgl_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                    ]),
                Fieldset::make('Data Pendaftar')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.nama_lengkap', $state);
                            }),
                        TextInput::make('nama_baptis')
                            ->required()
                            ->label('Nama Baptis')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.nama_baptis', $state);
                            }),
                        Radio::make('jenis_kelamin')
                            ->required()
                            ->label('Jenis Kelamin')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Pria' => 'Pria',
                                'Wanita' => 'Wanita'
                            ])
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.jenis_kelamin', $state);
                            }),
                        TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.tempat_lahir', $state);
                            }),
                        DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir')
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.tgl_lahir', $state);
                            }),
                        Textarea::make('alamat')
                            ->required()
                            ->label('Alamat Lengkap')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.alamat', $state);
                            }),
                        TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('Nomor Telepon')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.telepon', $state);
                            }),
                        Radio::make('agama_asal')
                            ->required()
                            ->label('Agama Asal')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Protestan' => 'Protestan',
                            ]),
                        Select::make('pendidikan_terakhir')
                            ->required()
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                                'SMA' => 'SMA',
                                'SMP' => 'SMP',
                                'SD' => 'SD',
                                'TK' => 'TK',
                                'Belum Sekolah' => 'Belum Sekolah',
                            ]),
                        DatePicker::make('tgl_belajar')
                            ->required()
                            ->label('Tanggal Mulai Pembelajaran'),
                        TextInput::make('wali_baptis')
                            ->required()
                            ->label('Nama Wali Baptis')
                            ->maxLength(255),
                        Textarea::make('alasan_masuk')
                            ->required()
                            ->label('Alasan Masuk Katolik')
                            ->columnSpanFull(),
                        DatePicker::make('tgl_baptis')
                            ->required(),       
                    ]),
                    
                Fieldset::make('Data Keluarga')
                    ->schema([
                        Hidden::make('keluarga_id'),
                        TextInput::make('nama_ayah')
                            ->required()
                            ->label('Nama Ayah')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.nama_ayah', $state);
                            }),
                        Select::make('agama_ayah')
                            ->required()
                            ->label('Agama Ayah')
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                            ])
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.agama_ayah', $state);
                            }),
                        TextInput::make('nama_ibu')
                            ->required()
                            ->label('Nama Ibu')
                            ->maxLength(255)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.nama_ibu', $state);
                            }),
                        Select::make('agama_ibu')
                            ->required()
                            ->label('Agama Ibu')
                            ->options([
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                            ])
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.agama_ibu', $state);
                            }),
                        Fieldset::make('Anggota Keluarga yang sudah Katolik')
                            ->schema([
                                TextInput::make('nama_keluarga1')
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 1'),
                                Select::make('hub_keluarga1')
                                    ->label('Hubungan Keluarga 1')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                                TextInput::make('nama_keluarga2')
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 2'),
                                Select::make('hub_keluarga2')
                                    ->label('Hubungan Keluarga 2')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                            ]),
                        Textarea::make('alamat_keluarga')
                            ->required()
                            ->label('Alamat Keluarga')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('data.alamat_keluarga', $state);
                            }),
                        SignaturePad::make('ttd_ortu')
                            ->required()
                            ->label('Tanda Tangan Orang Tua (Ayah)'),
                        Hidden::make('nama_pastor'),
                        Hidden::make('ttd_pastor'),
                        Hidden::make('ttd_ketua'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        // Simpan tanda tangan orang tua
        $image = $this->data['ttd_ortu'];  
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.'png';
        File::put(storage_path(). '/' . $imageName, base64_decode($image));
        $this->data['ttd_ortu'] = $imageName;

        $data = $this->form->getState();
        /** @var User $user */
        $user = Auth::user();

        // Update atau create data user
        $user->update([
            'name' => $data['nama_lengkap'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tgl_lahir' => $data['tgl_lahir'],
            'telepon' => $data['telepon'],
        ]);

        // Update atau create detail user
        $user->detailUser()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_baptis' => $data['nama_baptis'],
                'tgl_baptis' => $data['tgl_baptis'],
                'alamat' => $data['alamat'],
                'lingkungan_id' => $data['lingkungan_id'],
            ]
        );

        // Update atau create data keluarga
        $keluargaData = [
            'nama_ayah' => $data['nama_ayah'],
            'agama_ayah' => $data['agama_ayah'],
            'nama_ibu' => $data['nama_ibu'],
            'agama_ibu' => $data['agama_ibu'],
            'alamat_ayah' => $data['alamat_keluarga'],
            'alamat_ibu' => $data['alamat_keluarga'],
            'ttd_ayah' => $data['ttd_ortu'],
        ];

        if (isset($data['keluarga_id'])) {
            Keluarga::where('id', $data['keluarga_id'])->update($keluargaData);
        } else {
            $keluarga = Keluarga::create($keluargaData);
            DetailUser::where('user_id', $user->id)->update(['keluarga_id' => $keluarga->id]);
        }

        // Buat pendaftaran baptis
        $pendaftaranBaptis = PendaftaranBaptis::create($data);
        
        // Buat surat
        $surat = Surat::create([
            'user_id' => $user->id,
            'lingkungan_id' => $data['lingkungan_id'],
            'jenis_surat' => 'pendaftaran_baptis',
            'perihal' => 'Pendaftaran Baptis',
            'tgl_surat' => $data['tgl_surat'] ?? now(),
            'status' => 'menunggu',
        ]);
        
        if ($surat) {
            $pendaftaranBaptis->update(['surat_id' => $surat->id]);
        }

        Notification::make()
            ->title('Pengajuan Pendaftaran Baptis berhasil dibuat')
            ->success()
            ->send();
            
        $this->form->fill();
    }
}