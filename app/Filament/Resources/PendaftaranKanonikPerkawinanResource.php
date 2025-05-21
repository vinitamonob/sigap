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
use Illuminate\Support\Str;
use App\Models\KetuaLingkungan;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Services\SuratKanonikGenerate;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
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
                        Forms\Components\TextInput::make('nama_istri')
                            ->required()
                            ->label('Nama lengkap Calon Istri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('akun_email_istri')
                            ->required()
                            ->label('Akun Email Calon Istri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir_istri')
                            ->required()
                            ->label('Tempat Lahir Calon Istri')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tgl_lahir_istri')
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
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                                'SMA' => 'SMA',
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
                        Forms\Components\DatePicker::make('tgl_baptis_istri')
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
                                    Forms\Components\TextInput::make('nama_lingkungan_istri')
                                        ->label('Nama Lingkungan (Jika tidak ada di pilihan)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('wilayah_istri')
                                        ->label('Wilayah')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('paroki_istri')
                                        ->label('Paroki')
                                        ->maxLength(255),
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
                            Forms\Components\TextInput::make('nama_suami')
                                ->required()
                                ->label('Nama Calon Suami')
                                ->maxLength(255),
                        Forms\Components\TextInput::make('akun_email_suami')
                            ->required()
                            ->label('Akun Email Calon Suami')
                            ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_lahir_suami')
                                ->required()
                                ->label('Tempat Lahir Calon Suami')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tgl_lahir_suami')
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
                                    'Diploma/Sarjana' => 'Diploma/Sarjana',
                                    'SMA' => 'SMA',
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
                            Forms\Components\DatePicker::make('tgl_baptis_suami')
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
                                                        $set('ketua_lingkungan_id', $ketuaLingkungan->id);
                                                    }
                                                }
                                            }),
                                        Forms\Components\TextInput::make('nama_lingkungan_suami')
                                            ->required()
                                            ->label('Nama Lingkungan (Jika tidak ada di pilihan)')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('wilayah_suami')
                                            ->required()
                                            ->label('Wilayah')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('paroki_suami')
                                            ->required()
                                            ->label('Paroki')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('nama_ketua_suami')
                                            ->required()
                                            ->label('Nama Ketua Lingkungan Calon Suami')
                                            ->maxLength(255),
                                        SignaturePad::make('ttd_ketua_suami')
                                            ->label('Tanda Tangan Ketua Lingkungan Calon Suami'),
                                        Forms\Components\Hidden::make('ketua_lingkungan_id'),   
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
                                    ->default(now())
                                    ->readOnly(),
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
                Tables\Columns\TextColumn::make('calonSuami.user.name')
                    ->label('Calon Suami')
                    ->searchable(),   
                Tables\Columns\TextColumn::make('calonIstri.user.name')
                    ->label('Calon Istri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lokasi_gereja')
                    ->label('Lokasi Gereja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_pernikahan')
                    ->label('Tanggal Nikah')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                            $ketuaLingkungan = KetuaLingkungan::where('user_id', $user->id)
                                ->where('aktif', true)
                                ->first();
                            
                            if (!$ketuaLingkungan) {
                                Notification::make()
                                    ->title('Error: Anda bukan ketua lingkungan aktif')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $lingkungan = $ketuaLingkungan->lingkungan;
                            
                            // Generate nomor surat
                            $tahun = Carbon::now()->format('Y');
                            $bulan = Carbon::now()->format('m');
                            $kode = $lingkungan->kode ?? 'XX';
                            
                            $count = 1;
                            do {
                                $nomor_surat = sprintf('%04d/KP/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = PendaftaranKanonikPerkawinan::where('nomor_surat', $nomor_surat)->exists();
                                $count = $exists ? $count + 1 : $count;
                            } while ($exists);
                            
                            // Update data dengan pengecekan lingkungan
                            $updateData = ['nomor_surat' => $nomor_surat];
                            
                            // Cek apakah user adalah ketua lingkungan untuk calon suami
                            if ($record->lingkungan_suami_id == $ketuaLingkungan->lingkungan_id) {
                                $updateData['ttd_ketua_suami'] = $user->tanda_tangan;
                            }
                            
                            // Cek apakah user adalah ketua lingkungan untuk calon istri
                            if ($record->lingkungan_istri_id == $ketuaLingkungan->lingkungan_id) {
                                $updateData['ttd_ketua_istri'] = $user->tanda_tangan;
                            }
                            
                            $record->update($updateData);
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'nomor_surat' => $nomor_surat,
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat Pendaftaran Kanonik & Perkawinan diterima')
                                ->success()
                                ->send();
                        }
                        elseif ($user->hasRole('paroki')) {
                            $lingkungan = $record->lingkungan;

                            $record->update([
                                'ttd_pastor' => $user->tanda_tangan,
                                'nama_pastor' => $user->name,
                            ]);
                            
                            try {
                                // Generate file surat
                                $namaLingkungan = $lingkungan ? $lingkungan->nama_lingkungan : '';
                                $namaLingkunganSlug = Str::slug($namaLingkungan);

                                $templatePath = 'templates/surat_pendaftaran_kanonik_perkawinan.docx';
                                $namaSurat = $namaLingkunganSlug . '-' . now()->format('d-m-Y-h-m-s') . '-surat_pendaftaran_kanonik_perkawinan.docx';
                                $outputPath = storage_path('app/public/' . $namaSurat);
                            
                                // Data untuk template surat
                                $data = [
                                    'nomor_surat' => $record->nomor_surat,
                                    'tgl_surat' => $record->tgl_surat->locale('id')->translatedFormat('d F Y'),
                                    'lokasi_gereja' => $record->lokasi_gereja,
                                    'tgl_pernikahan' => $record->tgl_pernikahan->locale('id')->translatedFormat('d F Y'),
                                    'waktu_pernikahan' => $record->waktu_pernikahan->format('H:i'),
                                    'lingkungan_istri' => $record->lingkunganIstri->nama_lingkungan ?? $record->calonIstri->nama_lingkungan ?? '',
                                    'wilayah_istri' => $record->lingkunganIstri->wilayah ?? $record->calonIstri->wilayah ?? '',
                                    'paroki_istri' => $record->lingkunganIstri->paroki ?? $record->calonIstri->paroki ?? '',
                                    'nama_istri' => $record->calonIstri->user->name,
                                    'tempat_lahir_istri' => $record->calonIstri->user->tempat_lahir,
                                    'tgl_lahir_istri' => $record->calonIstri->user->tgl_lahir->locale('id')->translatedFormat('d F Y'),
                                    'tempat_baptis_istri' => $record->calonIstri->user->detailUser->tempat_baptis,
                                    'tgl_baptis_istri' => $record->calonIstri->user->detailUser->tgl_baptis->locale('id')->translatedFormat('d F Y'),
                                    'alamat_skrng_istri' => $record->calonIstri->user->detailUser->alamat,
                                    'alamat_stlh_menikah_istri' => $record->calonIstri->alamat_stlh_menikah,
                                    'telepon_istri' => $record->calonIstri->user->telepon,
                                    'pekerjaan_istri' => $record->calonIstri->pekerjaan,
                                    'pendidikan_terakhir_istri' => $record->calonIstri->pendidikan_terakhir,
                                    'agama_istri' => $record->calonIstri->agama,
                                    'nama_ayah_istri' => $record->calonIstri->keluarga->nama_ayah,
                                    'agama_ayah_istri' => $record->calonIstri->keluarga->agama_ayah,
                                    'pekerjaan_ayah_istri' => $record->calonIstri->keluarga->pekerjaan_ayah,
                                    'alamat_ayah_istri' => $record->calonIstri->keluarga->alamat_ayah,
                                    'nama_ibu_istri' => $record->calonIstri->keluarga->nama_ibu,
                                    'agama_ibu_istri' => $record->calonIstri->keluarga->agama_ibu,
                                    'pekerjaan_ibu_istri' => $record->calonIstri->keluarga->pekerjaan_ibu,
                                    'alamat_ibu_istri' => $record->calonIstri->keluarga->alamat_ibu,
                                    'lingkungan_suami' => $record->lingkunganSuami->nama_lingkungan ?? $record->calonSuami->nama_lingkungan?? '',
                                    'wilayah_suami' => $record->lingkunganSuami->wilayah ?? $record->calonSuami->wilayah ?? '',
                                    'paroki_suami' => $record->lingkunganSuami->paroki ?? $record->calonSuami->paroki ?? '',
                                    'nama_suami' => $record->calonSuami->user->name,
                                    'tempat_lahir_suami' => $record->calonSuami->user->tempat_lahir,
                                    'tgl_lahir_suami' => $record->calonSuami->user->tgl_lahir->locale('id')->translatedFormat('d F Y'),
                                    'tempat_baptis_suami' => $record->calonSuami->user->detailUser->tempat_baptis,
                                    'tgl_baptis_suami' => $record->calonSuami->user->detailUser->tgl_baptis->locale('id')->translatedFormat('d F Y'),
                                    'alamat_skrng_suami' => $record->calonSuami->user->detailUser->alamat,
                                    'alamat_stlh_menikah_suami' => $record->calonSuami->alamat_stlh_menikah,
                                    'telepon_suami' => $record->calonSuami->user->telepon,
                                    'pekerjaan_suami' => $record->calonSuami->pekerjaan,
                                    'pendidikan_terakhir_suami' => $record->calonSuami->pendidikan_terakhir,
                                    'agama_suami' => $record->calonSuami->agama,
                                    'nama_ayah_suami' => $record->calonSuami->keluarga->nama_ayah,
                                    'agama_ayah_suami' => $record->calonSuami->keluarga->agama_ayah,
                                    'pekerjaan_ayah_suami' => $record->calonSuami->keluarga->pekerjaan_ayah,
                                    'alamat_ayah_suami' => $record->calonSuami->keluarga->alamat_ayah,
                                    'nama_ibu_suami' => $record->calonSuami->keluarga->nama_ibu,
                                    'agama_ibu_suami' => $record->calonSuami->keluarga->agama_ibu,
                                    'pekerjaan_ibu_suami' => $record->calonSuami->keluarga->pekerjaan_ibu,
                                    'alamat_ibu_suami' => $record->calonSuami->keluarga->alamat_ibu,
                                    'nama_ketua_istri' => $record->calonIstri->nama_ketua ?? $record->calonIstri->ketuaLingkungan->user->name ?? '',
                                    'nama_ketua_suami' => $record->calonSuami->nama_ketua ?? $record->calonSuami->ketuaLingkungan->user->name ?? '',
                                    'nama_pastor' => $user->name,
                                    'ttd_calon_istri' => $record->ttd_calon_istri,
                                    'ttd_ketua_istri' => $record->ttd_ketua_istri,
                                    'ttd_calon_suami' => $record->ttd_calon_suami,
                                    'ttd_ketua_suami' => $record->ttd_ketua_suami,
                                    'ttd_pastor' => $user->tanda_tangan,
                                ];
                                
                                // Generate surat (sesuaikan dengan class generator Anda)
                                $generateSurat = (new SuratKanonikGenerate)->generateFromTemplate(
                                    $templatePath,  
                                    $outputPath,
                                    $data,
                                    public_path($record->ttd_calon_suami),
                                    public_path($record->ttd_calon_istri),
                                    public_path($record->ttd_ketua_suami),
                                    public_path($record->ttd_ketua_istri),
                                    public_path($user->tanda_tangan)
                                );
                            
                                $surat = Surat::where('id', $record->surat_id)
                                                ->where('status', 'menunggu')
                                                ->first();
                                // dd($surat, $record);
                                if ($surat) {
                                    $surat->update([
                                        'nomor_surat' => $record->nomor_surat,
                                        'status' => 'selesai',
                                        'file_surat' => $namaSurat,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                // dd($e);
                                logger()->error($e);
                            }
                            
                            Notification::make()
                                ->title('Surat Pendaftaran Kanonik & Perkawinan telah disetujui Pastor')
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