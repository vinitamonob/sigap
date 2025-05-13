<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Surat;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use App\Models\Keluarga;
use Illuminate\Support\Str;
use App\Models\KetuaLingkungan;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\CalonPasangan;
use App\Models\PendaftaranKanonikPerkawinan;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;

class PendaftaranKanonikPerkawinanResource extends Resource
{
    protected static ?string $model = PendaftaranKanonikPerkawinan::class;

    protected static ?string $navigationGroup = 'Surat';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Calon Istri')
                    ->schema([
                        Forms\Components\Select::make('calon_istri_id')
                            ->label('Pilih Calon Istri (Opsional)')
                            ->options(function () {
                                return CalonPasangan::with(['user', 'lingkungan'])
                                    ->where('jenis_kelamin', 'Wanita')
                                    ->get()
                                    ->mapWithKeys(function ($calon) {
                                        return [$calon->id => $calon->user->name . ' - ' . ($calon->lingkungan->nama_lingkungan ?? '')];
                                    });
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $calonIstri = CalonPasangan::with([
                                        'user', 
                                        'lingkungan',
                                        'ketuaLingkungan.user',
                                        'keluarga'
                                    ])->find($state);

                                    if ($calonIstri) {
                                        // Data pribadi
                                        $set('nama_istri', $calonIstri->user->name);
                                        $set('tempat_lahir_istri', $calonIstri->user->tempat_lahir);
                                        $set('tanggal_lahir_istri', $calonIstri->user->tgl_lahir);
                                        $set('telepon_istri', $calonIstri->user->telepon);
                                        $set('agama_istri', $calonIstri->agama);
                                        $set('pekerjaan_istri', $calonIstri->pekerjaan);
                                        $set('pendidikan_terakhir_istri', $calonIstri->pendidikan_terakhir);

                                        // Data lingkungan
                                        if ($calonIstri->lingkungan) {
                                            $set('nama_lingkungan_istri', $calonIstri->lingkungan->nama_lingkungan);
                                            $set('wilayah_istri', $calonIstri->lingkungan->wilayah);
                                            $set('paroki_istri', $calonIstri->lingkungan->paroki);
                                            $set('lingkungan_istri_id', $calonIstri->lingkungan_id);
                                        }

                                        // Data ketua lingkungan
                                        if ($calonIstri->ketuaLingkungan) {
                                            $set('nama_ketua_istri', $calonIstri->ketuaLingkungan->user->name);
                                            $set('ketua_lingkungan_istri_id', $calonIstri->ketua_lingkungan_id);
                                        }

                                        // Data keluarga
                                        if ($calonIstri->keluarga) {
                                            $set('nama_ayah_istri', $calonIstri->keluarga->nama_ayah);
                                            $set('agama_ayah_istri', $calonIstri->keluarga->agama_ayah);
                                            $set('pekerjaan_ayah_istri', $calonIstri->keluarga->pekerjaan_ayah);
                                            $set('alamat_ayah_istri', $calonIstri->keluarga->alamat_ayah);
                                            $set('nama_ibu_istri', $calonIstri->keluarga->nama_ibu);
                                            $set('agama_ibu_istri', $calonIstri->keluarga->agama_ibu);
                                            $set('pekerjaan_ibu_istri', $calonIstri->keluarga->pekerjaan_ibu);
                                            $set('alamat_ibu_istri', $calonIstri->keluarga->alamat_ibu);
                                            $set('keluarga_istri_id', $calonIstri->keluarga_id);
                                        }

                                        // Data detail user
                                        if ($calonIstri->user->detailUser) {
                                            $set('alamat_sekarang_istri', $calonIstri->user->detailUser->alamat);
                                        }
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('nama_istri')
                            ->required()
                            ->label('Nama lengkap Calon Istri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir_istri')
                            ->required()
                            ->label('Tempat Lahir Calon Istri')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir_istri')
                            ->required()
                            ->label('Tanggal Lahir Calon Istri'),
                        Forms\Components\Textarea::make('alamat_sekarang_istri')
                            ->required()
                            ->label('Alamat Calon Istri')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('alamat_setelah_menikah_istri')
                            ->required()
                            ->label('Alamat Calon Istri Setelah Menikah')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('telepon_istri')
                            ->tel()
                            ->required()
                            ->label('Telepon Calon Istri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pekerjaan_istri')
                            ->required()
                            ->label('Pekerjaan Calon Istri')
                            ->maxLength(255),
                        Forms\Components\Select::make('pendidikan_terakhir_istri')
                            ->required()
                            ->label('Pendidikan Terakhir Calon Istri')
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                            ]),
                        Forms\Components\Select::make('agama_istri')
                            ->required()
                            ->label('Agama Calon Istri')
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                            ]),
                        Forms\Components\TextInput::make('tempat_baptis_istri')
                            ->maxLength(255)
                            ->label('Tempat Baptis Calon Istri'),
                        Forms\Components\DatePicker::make('tanggal_baptis_istri')
                            ->label('Tanggal Baptis Calon Istri'),
                        SignaturePad::make('ttd_calon_istri')
                            ->label('Tanda Tangan Calon Istri'),

                        Fieldset::make('Data Orang Tua Calon Istri')
                            ->schema([
                                Forms\Components\TextInput::make('nama_ayah_istri')
                                    ->required()
                                    ->label('Nama Ayah Calon Istri')
                                    ->maxLength(255),
                                Forms\Components\Select::make('agama_ayah_istri')
                                    ->required()
                                    ->label('Agama Ayah Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                Forms\Components\TextInput::make('pekerjaan_ayah_istri')
                                    ->required()
                                    ->label('Pekerjaan Ayah Calon Istri')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat_ayah_istri')
                                    ->required()
                                    ->label('Alamat Ayah Calon Istri')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('nama_ibu_istri')
                                    ->required()
                                    ->label('Nama Ibu Calon Istri')
                                    ->maxLength(255),
                                Forms\Components\Select::make('agama_ibu_istri')
                                    ->required()
                                    ->label('Agama Ibu Calon Istri')
                                    ->options([
                                        'Katolik' => 'Katolik',
                                        'Protestan' => 'Protestan',
                                        'Islam' => 'Islam',
                                        'Hindu' => 'Hindu',
                                        'Budha' => 'Budha',
                                    ]),
                                Forms\Components\TextInput::make('pekerjaan_ibu_istri')
                                    ->required()
                                    ->label('Pekerjaan Ibu Calon Istri')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat_ibu_istri')
                                    ->required()
                                    ->label('Alamat Ibu Calon Istri')
                                    ->columnSpanFull(),
                            ]),   
                            Fieldset::make('Data Lingkungan Calon Istri')
                                ->schema([
                                    Forms\Components\Select::make('lingkungan_istri_id')
                                        ->label('Lingkungan/Stasi Calon Istri')
                                        ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $lingkungan = Lingkungan::find($state);
                                                if ($lingkungan) {
                                                    $set('nama_lingkungan_istri', $lingkungan->nama_lingkungan);
                                                    $set('wilayah_istri', $lingkungan->wilayah);
                                                    $set('paroki_istri', $lingkungan->paroki);
                                                }
                                            }
                                        }),
                                    Forms\Components\Hidden::make('nama_lingkungan_istri'),
                                    Forms\Components\Hidden::make('wilayah_istri'),
                                    Forms\Components\Hidden::make('paroki_istri'),
                                    Forms\Components\TextInput::make('nama_ketua_istri')
                                        ->required()
                                        ->label('Nama Ketua Lingkungan Calon Istri')
                                        ->maxLength(255),
                                    SignaturePad::make('ttd_ketua_istri')
                                        ->label('Tanda Tangan Ketua Lingkungan Calon Istri'),
                                ])                          
                    ]),
                    Fieldset::make('Data Calon Suami')
                        ->schema([
                            Forms\Components\Select::make('calon_suami_id')
                                ->label('Pilih Calon Suami (Opsional)')
                                ->options(function () {
                                    return CalonPasangan::with(['user', 'lingkungan'])
                                        ->where('jenis_kelamin', 'Pria')
                                        ->get()
                                        ->mapWithKeys(function ($calon) {
                                            return [$calon->id => $calon->user->name . ' - ' . ($calon->lingkungan->nama_lingkungan ?? '')];
                                        });
                                })
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $calonSuami = CalonPasangan::with([
                                            'user', 
                                            'lingkungan',
                                            'ketuaLingkungan.user',
                                            'keluarga'
                                        ])->find($state);

                                        if ($calonSuami) {
                                            // Data pribadi
                                            $set('nama_suami', $calonSuami->user->name);
                                            $set('tempat_lahir_suami', $calonSuami->user->tempat_lahir);
                                            $set('tanggal_lahir_suami', $calonSuami->user->tgl_lahir);
                                            $set('telepon_suami', $calonSuami->user->telepon);
                                            $set('agama_suami', $calonSuami->agama);
                                            $set('pekerjaan_suami', $calonSuami->pekerjaan);
                                            $set('pendidikan_terakhir_suami', $calonSuami->pendidikan_terakhir);

                                            // Data lingkungan
                                            if ($calonSuami->lingkungan) {
                                                $set('nama_lingkungan_suami', $calonSuami->lingkungan->nama_lingkungan);
                                                $set('wilayah_suami', $calonSuami->lingkungan->wilayah);
                                                $set('paroki_suami', $calonSuami->lingkungan->paroki);
                                                $set('lingkungan_suami_id', $calonSuami->lingkungan_id);
                                            }

                                            // Data ketua lingkungan
                                            if ($calonSuami->ketuaLingkungan) {
                                                $set('nama_ketua_suami', $calonSuami->ketuaLingkungan->user->name);
                                                $set('ketua_lingkungan_suami_id', $calonSuami->ketua_lingkungan_id);
                                            }

                                            // Data keluarga
                                            if ($calonSuami->keluarga) {
                                                $set('nama_ayah_suami', $calonSuami->keluarga->nama_ayah);
                                                $set('agama_ayah_suami', $calonSuami->keluarga->agama_ayah);
                                                $set('pekerjaan_ayah_suami', $calonSuami->keluarga->pekerjaan_ayah);
                                                $set('alamat_ayah_suami', $calonSuami->keluarga->alamat_ayah);
                                                $set('nama_ibu_suami', $calonSuami->keluarga->nama_ibu);
                                                $set('agama_ibu_suami', $calonSuami->keluarga->agama_ibu);
                                                $set('pekerjaan_ibu_suami', $calonSuami->keluarga->pekerjaan_ibu);
                                                $set('alamat_ibu_suami', $calonSuami->keluarga->alamat_ibu);
                                                $set('keluarga_suami_id', $calonSuami->keluarga_id);
                                            }

                                            // Data detail user
                                            if ($calonSuami->user->detailUser) {
                                                $set('alamat_sekarang_suami', $calonSuami->user->detailUser->alamat);
                                            }
                                        }
                                    }
                                }),
                            Forms\Components\TextInput::make('nama_suami')
                                ->required()
                                ->label('Nama Calon Suami')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_lahir_suami')
                                ->required()
                                ->label('Tempat Lahir Calon Suami')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_lahir_suami')
                                ->required()
                                ->label('Tanggal Lahir Calon Suami'),
                            Forms\Components\Textarea::make('alamat_sekarang_suami')
                                ->required()
                                ->label('Alamat Sekarang Calon Suami')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('alamat_setelah_menikah_suami')
                                ->required()
                                ->label('Alamat Setelah Menikah Calon Suami')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('telepon_suami')
                                ->tel()
                                ->required()
                                ->label('Telepon Calon Suami')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pekerjaan_suami')
                                ->required()
                                ->label('Pekerjaan Calon Suami')
                                ->maxLength(255),
                            Forms\Components\Select::make('pendidikan_terakhir_suami')
                                ->required()
                                ->label('Pendidikan Terakhir Calon Suami')
                                ->options([
                                    'TK' => 'TK',
                                    'SD' => 'SD',
                                    'SMP' => 'SMP',
                                    'SMA' => 'SMA',
                                    'Diploma/Sarjana' => 'Diploma/Sarjana',
                                ]),
                            Forms\Components\Select::make('agama_suami')
                                ->required()
                                ->label('Agama Calon Suami')
                                ->options([
                                    'Katolik' => 'Katolik',
                                    'Protestan' => 'Protestan',
                                    'Islam' => 'Islam',
                                    'Hindu' => 'Hindu',
                                    'Budha' => 'Budha',
                                ]),
                            Forms\Components\TextInput::make('tempat_baptis_suami')
                                ->maxLength(255)
                                ->label('Tempat Baptis Calon Suami'),
                            Forms\Components\DatePicker::make('tanggal_baptis_suami')
                                ->label('Tanggal Baptis Calon Suami'),
                            SignaturePad::make('ttd_calon_suami')
                                ->label('Tanda Tangan Calon Suami'),
                            Fieldset::make('Data Orang Tua Calon Suami')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_ayah_suami')
                                        ->required()
                                        ->label('Nama Ayah Calon Suami')
                                        ->maxLength(255),
                                    Forms\Components\Select::make('agama_ayah_suami')
                                        ->required()
                                        ->label('Agama Ayah Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    Forms\Components\TextInput::make('pekerjaan_ayah_suami')
                                        ->required()
                                        ->label('Pekerjaan Ayah Calon Suami')
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('alamat_ayah_suami')
                                        ->required()
                                        ->label('Alamat Ayah Calon Suami')
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('nama_ibu_suami')
                                        ->required()
                                        ->label('Nama Ibu Calon Suami')
                                        ->maxLength(255),
                                    Forms\Components\Select::make('agama_ibu_suami')
                                        ->required()
                                        ->label('Agama Ibu Calon Suami')
                                        ->options([
                                            'Katolik' => 'Katolik',
                                            'Protestan' => 'Protestan',
                                            'Islam' => 'Islam',
                                            'Hindu' => 'Hindu',
                                            'Budha' => 'Budha',
                                        ]),
                                    Forms\Components\TextInput::make('pekerjaan_ibu_suami')
                                        ->required()
                                        ->label('Pekerjaan Ibu Calon Suami')
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('alamat_ibu_suami')
                                        ->required()
                                        ->label('Alamat Ibu Calon Suami')
                                        ->columnSpanFull(),
                                ]),
                                Fieldset::make('Data Lingkungan Calon Suami')
                                    ->schema([
                                        Forms\Components\Select::make('lingkungan_suami_id')
                                            ->label('Lingkungan/Stasi Calon Suami')
                                            ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $lingkungan = Lingkungan::find($state);
                                                    if ($lingkungan) {
                                                        $set('nama_lingkungan_suami', $lingkungan->nama_lingkungan);
                                                        $set('wilayah_suami', $lingkungan->wilayah);
                                                        $set('paroki_suami', $lingkungan->paroki);
                                                    }
                                                }
                                            }),
                                        Forms\Components\Hidden::make('nama_lingkungan_suami'),
                                        Forms\Components\Hidden::make('wilayah_suami'),
                                        Forms\Components\Hidden::make('paroki_suami'),
                                        Forms\Components\TextInput::make('nama_ketua_suami')
                                            ->required()
                                            ->label('Nama Ketua Lingkungan Calon Suami')
                                            ->maxLength(255),
                                        SignaturePad::make('ttd_ketua_suami')
                                            ->label('Tanda Tangan Ketua Lingkungan Calon Suami'),
                                    ])
                        ]),
                        Fieldset::make('Data Perkawinan')
                            ->schema([
                                Forms\Components\TextInput::make('lokasi_gereja')
                                    ->required()
                                    ->label('Lokasi Gereja')
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('tgl_pernikahan')
                                    ->required()
                                    ->label('Tanggal Pernikahan'),
                                Forms\Components\TimePicker::make('waktu_pernikahan')
                                    ->required()
                                    ->label('Waktu Pernikahan'),
                                Forms\Components\DatePicker::make('tgl_surat')
                                    ->required()
                                    ->label('Tanggal Surat')
                                    ->default(now()),
                                Forms\Components\Hidden::make('nomor_surat'),
                                Forms\Components\Hidden::make('nama_pastor'),
                                Forms\Components\Hidden::make('ttd_pastor'),
                            ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::where('id', Auth::user()->id)->first();
                
                if ($user->hasRole('super_admin')) {
                    return $query;
                }
                
                if ($user->hasRole('paroki')) {
                    return $query->whereNotNull('nomor_surat')
                                ->whereNotNull('ttd_ketua_suami')
                                ->whereNotNull('ttd_ketua_istri');
                }
                
                if ($user->hasRole('ketua_lingkungan')) {
                    $ketuaLingkungan = KetuaLingkungan::where('user_id', $user->id)
                        ->where('aktif', true)
                        ->first();
                    
                    if ($ketuaLingkungan) {
                        return $query->where(function($query) use ($ketuaLingkungan) {
                            $query->where('lingkungan_suami_id', $ketuaLingkungan->lingkungan_id)
                                  ->orWhere('lingkungan_istri_id', $ketuaLingkungan->lingkungan_id);
                        });
                    }
                }
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_istri')
                    ->label('Nama Calon Istri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paroki_istri')
                    ->label('Paroki Istri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_suami')
                    ->label('Nama Calon Suami')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paroki_suami')
                    ->label('Paroki Suami')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lokasi_gereja')
                    ->label('Lokasi Gereja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_pernikahan')
                    ->label('Tanggal Nikah')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_pernikahan')
                    ->label('Waktu Nikah'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'menunggu_paroki' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label(fn($record) => match(true) {
                        User::where('id', Auth::user()->id)->first()->hasRole('ketua_lingkungan') && $record->nomor_surat === null => 'Accept',
                        User::where('id', Auth::user()->id)->first()->hasRole('paroki') && $record->ttd_pastor === null => 'Accept',
                        default => 'Done'
                    })
                    ->color(fn($record) => match(true) {
                        User::where('id', Auth::user()->id)->first()->hasRole('ketua_lingkungan') && $record->nomor_surat === null => 'warning',
                        User::where('id', Auth::user()->id)->first()->hasRole('paroki') && $record->ttd_pastor === null => 'warning',
                        default => 'success'
                    })
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->disabled(fn($record) => match(true) {
                        User::where('id', Auth::user()->id)->first()->hasRole('ketua_lingkungan') => $record->nomor_surat !== null,
                        User::where('id', Auth::user()->id)->first()->hasRole('paroki') => $record->nomor_surat === null || $record->ttd_pastor !== null,
                        default => true
                    })
                    ->visible(fn() => !User::where('id', Auth::user()->id)->first()->hasRole('super_admin'))
                    ->action(function (PendaftaranKanonikPerkawinan $record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        
                        if ($user->hasRole('ketua_lingkungan')) {
                            // Generate nomor surat
                            $tahun = Carbon::now()->format('Y');
                            $bulan = Carbon::now()->format('m');
                            $count = PendaftaranKanonikPerkawinan::whereYear('created_at', $tahun)
                                ->whereMonth('created_at', $bulan)
                                ->count() + 1;
                            
                            $nomor_surat = sprintf('%03d/KK/LG/%s/%s', $count, $bulan, $tahun);
                            
                            // Update record
                            $record->update([
                                'nomor_surat' => $nomor_surat,
                                'ttd_ketua_suami' => $user->tanda_tangan,
                                'ttd_ketua_istri' => $user->tanda_tangan,
                            ]);
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'nomor_surat' => $nomor_surat,
                                    'status' => 'menunggu_paroki',
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat Pendaftaran Kanonik & Perkawinan diterima')
                                ->success()
                                ->send();
                        } 
                        elseif ($user->hasRole('paroki')) {
                            $record->update([
                                'ttd_pastor' => $user->tanda_tangan,
                                'nama_pastor' => $user->name,
                            ]);
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'status' => 'selesai',
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat Pendaftaran Kanonik & Perkawinan disetujui Pastor')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftaranKanonikPerkawinans::route('/'),
            'create' => Pages\CreatePendaftaranKanonikPerkawinan::route('/create'),
            'edit' => Pages\EditPendaftaranKanonikPerkawinan::route('/{record}/edit'),
        ];
    }
}