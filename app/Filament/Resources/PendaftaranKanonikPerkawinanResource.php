<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
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
                            
                            // Generate nomor surat
                            $tahun = Carbon::now()->format('Y');
                            $bulan = Carbon::now()->format('m');
                            $count = PendaftaranKanonikPerkawinan::whereYear('created_at', $tahun)
                                ->whereMonth('created_at', $bulan)
                                ->count() + 1;
                            
                            $nomor_surat = sprintf('%03d/KK/LG/%s/%s', $count, $bulan, $tahun);
                            
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
                            
                            // Generate file surat
                            $namaLingkungan = $record->lingkungan ? Str::slug($record->lingkungan->nama_lingkungan) : '';
                            $namaSurat = "surat-pendaftaran-perkawinan-{$namaLingkungan}-{$record->id}.docx";
                            $outputPath = storage_path("app/public/surat/{$namaSurat}");
                            $templatePath = base_path('templates/surat_pendaftaran_perkawinan.docx');
                            
                            // Data untuk template surat
                            $data = [
                                'nomor_surat' => $record->nomor_surat,
                                'tgl_surat' => $record->tgl_surat->format('d-m-Y'),
                                'lokasi_gereja' => $record->lokasi_gereja,
                                'tgl_pernikahan' => $record->tgl_pernikahan->format('d-m-Y'),
                                'waktu_pernikahan' => $record->waktu_pernikahan->format('H:i'),
                                'nama_pastor' => $user->name,
                                'ketua_istri' => $record->nama_ketua_istri,
                                'lingkungan_istri' => $record->nama_lingkungan_istri,
                                'wilayah_istri' => $record->wilayah_istri,
                                'paroki_istri' => $record->paroki_istri,
                                'nama_istri' => $record->nama_istri,
                                'tempat_lahir_istri' => $record->tempat_lahir_istri,
                                'tgl_lahir_istri' => $record->tgl_lahir_istri->format('d-m-Y'),
                                'alamat_skrng_istri' => $record->alamat_sekarang_istri,
                                'alamat_stlh_menikah_istri' => $record->alamat_setelah_menikah_istri,
                                'telepon_istri' => $record->telepon_istri,
                                'pekerjaan_istri' => $record->pekerjaan_istri,
                                'pendidikan_terakhir_istri' => $record->pendidikan_terakhir_istri,
                                'agama_istri' => $record->agama_istri,
                                'nama_ayah_istri' => $record->nama_ayah_istri,
                                'agama_ayah_istri' => $record->agama_ayah_istri,
                                'pekerjaan_ayah_istri' => $record->pekerjaan_ayah_istri,
                                'alamat_ayah_istri' => $record->alamat_ayah_istri,
                                'nama_ibu_istri' => $record->nama_ibu_istri,
                                'agama_ibu_istri' => $record->agama_ibu_istri,
                                'pekerjaan_ibu_istri' => $record->pekerjaan_ibu_istri,
                                'alamat_ibu_istri' => $record->alamat_ibu_istri,
                                'ketua_suami' => $record->nama_ketua_suami,
                                'lingkungan_suami' => $record->nama_lingkungan_suami,
                                'wilayah_suami' => $record->wilayah_suami,
                                'paroki_suami' => $record->paroki_suami,
                                'nama_suami' => $record->nama_suami,
                                'tempat_lahir_suami' => $record->tempat_lahir_suami,
                                'tgl_lahir_suami' => $record->tgl_lahir_suami->format('d-m-Y'),
                                'alamat_skrng_suami' => $record->alamat_sekarang_suami,
                                'alamat_stlh_menikah_suami' => $record->alamat_setelah_menikah_suami,
                                'telepon_suami' => $record->telepon_suami,
                                'pekerjaan_suami' => $record->pekerjaan_suami,
                                'pendidikan_terakhir_suami' => $record->pendidikan_terakhir_suami,
                                'agama_suami' => $record->agama_suami,
                                'nama_ayah_suami' => $record->nama_ayah_suami,
                                'agama_ayah_suami' => $record->agama_ayah_suami,
                                'pekerjaan_ayah_suami' => $record->pekerjaan_ayah_suami,
                                'alamat_ayah_suami' => $record->alamat_ayah_suami,
                                'nama_ibu_suami' => $record->nama_ibu_suami,
                                'agama_ibu_suami' => $record->agama_ibu_suami,
                                'pekerjaan_ibu_suami' => $record->pekerjaan_ibu_suami,
                                'alamat_ibu_suami' => $record->alamat_ibu_suami,
                                'ttd_calon_istri' => $record->ttd_ketua_istri,
                                'ttd_ketua_istri' => $record->ttd_ketua_istri,
                                'ttd_calon_suami' => $record->ttd_ketua_suami,
                                'ttd_ketua_suami' => $record->ttd_ketua_suami,
                                'ttd_pastor' => $user->tanda_tangan,
                            ];
                            
                            // Generate surat (sesuaikan dengan class generator Anda)
                            $generateSurat = (new SuratKanonikGenerate)->generateFromTemplate(
                                $templatePath,  
                                $outputPath,
                                $data,
                                'calon_istri',
                                'ketua_istri',
                                'calon_suami',
                                'ketua_suami',
                                'pastor'
                            );
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'status' => 'selesai',
                                    'file_surat' => "surat/{$namaSurat}",
                                ]);
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