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
use App\Models\PendaftaranBaptis;
use Illuminate\Support\Facades\Auth;
use App\Services\SuratBaptisGenerate;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\PendaftaranBaptisResource\Pages;

class PendaftaranBaptisResource extends Resource
{
    protected static ?string $model = PendaftaranBaptis::class;
    protected static ?string $navigationGroup = 'Pengajuan Surat';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Forms\Components\Hidden::make('nomor_surat'),
                        Forms\Components\Select::make('lingkungan_id')
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
                        Forms\Components\Hidden::make('nama_lingkungan'),
                        Forms\Components\Hidden::make('ketua_lingkungan_id'),
                        Forms\Components\TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->readOnly(), 
                        Forms\Components\DatePicker::make('tgl_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Umat (Opsional)')
                            ->options(function () {
                                return User::with('detailUser')->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => $user->name];
                                    });
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = User::with(['detailUser', 'detailUser.keluarga'])->find($state);
                                    if ($user) {
                                        $set('nama_lengkap', $user->name);
                                        $set('akun_email', $user->email);
                                        $set('jenis_kelamin', $user->jenis_kelamin);
                                        $set('tempat_lahir', $user->tempat_lahir);
                                        $set('tgl_lahir', Carbon::parse($user->tgl_lahir)->format('Y-m-d'));
                                        $set('telepon', $user->telepon);
                                        
                                        if ($user->detailUser) {
                                            $set('nama_baptis', $user->detailUser->nama_baptis);
                                            $set('alamat', $user->detailUser->alamat);
                                            
                                            if ($user->detailUser->keluarga) {
                                                $set('nama_ayah', $user->detailUser->keluarga->nama_ayah);
                                                $set('agama_ayah', $user->detailUser->keluarga->agama_ayah);
                                                $set('nama_ibu', $user->detailUser->keluarga->nama_ibu);
                                                $set('agama_ibu', $user->detailUser->keluarga->agama_ibu);
                                                $set('alamat_keluarga', $user->detailUser->keluarga->alamat_ayah);
                                                $set('ttd_ortu', $user->detailUser->keluarga->ttd_ayah);
                                            }
                                        }
                                    }
                                }
                            }),
                    ]),
                Fieldset::make('Data Pendaftar')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                            // ->validationMessages([
                            //     'regex' => 'Nama hanya boleh mengandung huruf dan spasi',
                            // ]),
                        Forms\Components\TextInput::make('akun_email')
                            ->required()
                            ->label('Akun Email')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_baptis')
                            ->required()
                            ->label('Nama Baptis')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\Radio::make('jenis_kelamin')
                            ->required()
                            ->label('Jenis Kelamin')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Pria' => 'Pria',
                                'Wanita' => 'Wanita'
                            ]),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir')
                            ->maxDate(now()),
                        Forms\Components\Textarea::make('alamat')
                            ->required()
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('Nomor Telepon')
                            ->maxLength(255),
                        Forms\Components\Radio::make('agama_asal')
                            ->required()
                            ->label('Agama Asal')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                                'Kepercayaan' => 'Kepercayaan',
                            ]),
                        Forms\Components\Select::make('pendidikan_terakhir')
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
                        Forms\Components\DatePicker::make('tgl_belajar')
                            ->required()
                            ->label('Tanggal Mulai Pembelajaran')
                            ->minDate(now()),
                        Forms\Components\TextInput::make('wali_baptis')
                            ->required()
                            ->label('Nama Wali Baptis')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alasan_masuk')
                            ->label('Alasan Masuk Katolik (Wajib diisi jika baptis dewasa)')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('tgl_baptis')
                            ->required()
                            ->minDate(now()),       
                    ]),
                Fieldset::make('Data Keluarga')
                    ->schema([
                        Forms\Components\TextInput::make('nama_ayah')
                            ->required()
                            ->label('Nama Ayah')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\Select::make('agama_ayah')
                            ->required()
                            ->label('Agama Ayah')
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                                'Kepercayaan' => 'Kepercayaan',
                            ]),
                        Forms\Components\TextInput::make('nama_ibu')
                            ->required()
                            ->label('Nama Ibu')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\Select::make('agama_ibu')
                            ->required()
                            ->label('Agama Ibu')
                            ->options([
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                                'Kepercayaan' => 'Kepercayaan',
                            ]),
                        Fieldset::make('Anggota Keluarga yang sudah Katolik')
                            ->schema([
                                Forms\Components\TextInput::make('nama_keluarga1')
                                    ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 1'),
                                Forms\Components\Select::make('hub_keluarga1')
                                    ->label('Hubungan Keluarga 1')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                                Forms\Components\TextInput::make('nama_keluarga2')
                                    ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                                    ->maxLength(255)
                                    ->label('Nama Keluarga 2'),
                                Forms\Components\Select::make('hub_keluarga2')
                                    ->label('Hubungan Keluarga 2')
                                    ->options([
                                        'Saudara Kandung' => 'Saudara Kandung',
                                        'Pasangan' => 'Pasangan',
                                        'Sepupu' => 'Sepupu',
                                        'Wali' => 'Wali',
                                        'Kerabat Lainnya' => 'Kerabat Lainnya',
                                    ]),
                            ]),
                        Forms\Components\Textarea::make('alamat_keluarga')
                            ->required()
                            ->label('Alamat Keluarga')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('ttd_ortu'),
                        Forms\Components\Hidden::make('nama_pastor'),
                        Forms\Components\Hidden::make('ttd_pastor'),
                        Forms\Components\Hidden::make('ttd_ketua'),
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
                                ->whereNotNull('ttd_ketua');
                }
                
                if ($user->hasRole('ketua_lingkungan')) {
                    $ketuaLingkungan = KetuaLingkungan::where('user_id', $user->id)
                        ->where('aktif', true)
                        ->first();
                    
                    if ($ketuaLingkungan) {
                        return $query->where('lingkungan_id', $ketuaLingkungan->lingkungan_id);
                    }
                }
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_surat')
                    ->label('Tanggal Surat')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lingkungan.nama_lingkungan')
                    ->label('Lingkungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_baptis')
                    ->label('Tanggal Baptis')
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
                        User::where('id', Auth::user()->id)->first()->hasRole('ketua_lingkungan') && $record->nomor_surat === null => 'TTD',
                        User::where('id', Auth::user()->id)->first()->hasRole('paroki') && $record->ttd_pastor === null => 'TTD',
                        default => 'Selesai'
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
                    ->action(function (PendaftaranBaptis $record) {
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
                                $nomor_surat = sprintf('%04d/PB/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = PendaftaranBaptis::where('nomor_surat', $nomor_surat)->exists();
                                $count = $exists ? $count + 1 : $count;
                            } while ($exists);
                            
                            $record->update([
                                'nomor_surat' => $nomor_surat,
                                'ttd_ketua' => $user->tanda_tangan,
                                'ketua_lingkungan_id' => $user->ketuaLingkungan->id,
                            ]);
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'nomor_surat' => $nomor_surat,
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat Pendaftaran Baptis diterima')
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

                                $templatePath = 'templates/surat_pendaftaran_baptis.docx';
                                $namaSurat = $namaLingkunganSlug . '-' . now()->format('d-m-Y-h-m-s') . '-surat_pendaftaran_baptis.docx';
                                $outputPath = storage_path('app/public/' . $namaSurat);
                                
                                $data = [
                                    'nomor_surat' => $record->nomor_surat,
                                    'nama_lengkap' => $record->user->name,
                                    'nama_baptis' => $record->user->detailUser->nama_baptis,
                                    'jenis_kelamin' => $record->user->jenis_kelamin,
                                    'tempat_lahir' => $record->user->tempat_lahir,
                                    'tgl_lahir' => $record->user->tgl_lahir->locale('id')->translatedFormat('d F Y'),
                                    'alamat' => $record->user->detailUser->alamat ?? '',
                                    'telepon' => $record->user->telepon,
                                    'agama_asal' => $record->agama_asal,
                                    'pendidikan_terakhir' => $record->pendidikan_terakhir,
                                    'nama_ayah' => $record->user->detailUser->keluarga->nama_ayah,
                                    'agama_ayah' => $record->user->detailUser->keluarga->agama_ayah,
                                    'nama_ibu' => $record->user->detailUser->keluarga->nama_ibu,
                                    'agama_ibu' => $record->user->detailUser->keluarga->agama_ibu,
                                    'nama_keluarga1' => $record->nama_keluarga1 ?? '-',
                                    'hub_keluarga1' => $record->hub_keluarga1 ?? '-',
                                    'nama_keluarga2' => $record->nama_keluarga2 ?? '-',
                                    'hub_keluarga2' => $record->hub_keluarga2 ?? '-',
                                    'alamat_keluarga' => $record->user->detailUser->keluarga->alamat_ayah,
                                    'tgl_belajar' => $record->tgl_belajar->locale('id')->translatedFormat('d F Y'),
                                    'wali_baptis' => $record->wali_baptis,
                                    'alasan_masuk' => $record->alasan_masuk ?? '-',
                                    'tgl_baptis' => $record->tgl_baptis->locale('id')->translatedFormat('d F Y'),
                                    'nama_lingkungan' => $record->lingkungan->nama_lingkungan,
                                    'paroki' => $record->lingkungan->paroki ?? 'St. Stephanus Cilacap',
                                    'nama_ketua' => $record->ketuaLingkungan->user->name,
                                    'nama_pastor' => $user->name,
                                    'tgl_surat' => $record->tgl_surat->locale('id')->translatedFormat('d F Y'),
                                    'ttd_ortu' => $record->ttd_ayah ?? '',
                                    'ttd_ketua' => $record->ttd_ketua ?? '',
                                    'ttd_pastor' => $user->tanda_tangan ?? '',
                                ];
                                // dd($record->ttd_ortu, $record->ttd_ketua, $user->tanda_tangan);
                                $generateSurat = (new SuratBaptisGenerate)->generateFromTemplate(
                                    $templatePath,  
                                    $outputPath,
                                    $data,
                                    $record->ttd_ortu ? public_path($record->ttd_ortu) : public_path('images/blank.png'),
                                    $record->ttd_ketua ? public_path($record->ttd_ketua) : public_path('images/blank.png'),
                                    $record->ttd_pastor ? public_path($user->tanda_tangan) : public_path('images/blank.png')
                                );
                                
                                // Update surat yang sudah ada
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
                                ->title('Surat Pendaftaran Baptis telah disetujui Pastor')
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
            'index' => Pages\ListPendaftaranBaptis::route('/'),
            'create' => Pages\CreatePendaftaranBaptis::route('/create'),
            'edit' => Pages\EditPendaftaranBaptis::route('/{record}/edit'),
        ];
    }
}