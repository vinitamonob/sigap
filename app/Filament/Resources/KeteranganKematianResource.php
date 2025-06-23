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
use App\Models\KeteranganKematian;
use Illuminate\Support\Facades\Auth;
use App\Services\SuratKematianGenerate;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KeteranganKematianResource\Pages;

class KeteranganKematianResource extends Resource
{
    protected static ?string $model = KeteranganKematian::class;
    protected static ?string $navigationGroup = 'Pengajuan Surat';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Administrasi')
                    ->schema([
                        Forms\Components\Hidden::make('surat_id'),
                        Forms\Components\Hidden::make('nomor_surat'),
                        Forms\Components\Select::make('lingkungan_id')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
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
                                        $set('paroki', $lingkungan->paroki ?? 'St. Stephanus Cilacap');
                                    }
                                    
                                    if ($ketuaLingkungan) {
                                        $set('ketua_lingkungan_id', $ketuaLingkungan->id);
                                    }
                                }
                            }),
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
                                        
                                        // Hitung usia jika ada tanggal lahir
                                        if ($user->tgl_lahir) {
                                            $set('usia', Carbon::parse($user->tgl_lahir)->age);
                                        }
                                        
                                        // Data dari detail user
                                        if ($user->detailUser) {
                                            $set('tempat_baptis', $user->detailUser->tempat_baptis ?? '');
                                            $set('no_baptis', $user->detailUser->no_baptis ?? '');
                                            $set('lingkungan_id', $user->detailUser->lingkungan_id);
                                            
                                            // Data dari keluarga
                                            if ($user->detailUser->keluarga) {
                                                $set('nama_ortu', $user->detailUser->keluarga->nama_ayah);
                                            }
                                        }
                                    }
                                }
                            }),
                    ]),
                Fieldset::make('Data Kematian')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\TextInput::make('usia')
                            ->required()
                            ->label('Usia')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('tempat_baptis')
                            ->required()
                            ->label('Tempat Baptis')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_baptis')
                            ->required()
                            ->label('No. Buku Baptis')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_ortu')
                            ->required()
                            ->label('Nama Orang Tua')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_pasangan')
                            ->label('Nama Pasangan')
                            ->regex('/^[\pL\s]+$/u') // Hanya menerima huruf dan spasi
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tgl_kematian')
                            ->required()
                            ->label('Tanggal Kematian'),
                        Forms\Components\DatePicker::make('tgl_pemakaman')
                            ->required()
                            ->label('Tanggal Pemakaman'),
                        Forms\Components\TextInput::make('tempat_pemakaman')
                            ->required()
                            ->label('Tempat Pemakaman')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pelayanan_sakramen')
                            ->required()
                            ->default('Perminyakan')
                            ->label('Pelayanan Sakramen')
                            ->readOnly(), 
                        Forms\Components\TextInput::make('sakramen')
                            ->required()
                            ->default('Minyak Suci')
                            ->label('Sakramen')
                            ->readOnly(),
                    ])
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
                    ->label('Lingkungan / Stasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_kematian')
                    ->label('Tanggal Kematian')
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
                    ->label(fn($record) => $record->nomor_surat === null ? 'TTD' : 'Selesai')
                    ->color(fn($record) => $record->nomor_surat === null ? 'warning' : 'success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->disabled(fn($record) => $record->nomor_surat !== null)
                    ->visible(fn() => !User::where('id', Auth::user()->id)->first()->hasRole('super_admin'))
                    ->action(function (KeteranganKematian $record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        
                        if ($user->hasRole('ketua_lingkungan')) {  
                            // Generate nomor surat
                            $tahun = Carbon::now()->format('Y');
                            $bulan = Carbon::now()->format('m');
                            
                            $lingkungan = $record->lingkungan;
                            $kode = $lingkungan ? $lingkungan->kode : 'XX';
                            
                            $count = 1;
                            do {
                                $nomor_surat = sprintf('%04d/KK/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = KeteranganKematian::where('nomor_surat', $nomor_surat)->exists();
                                if ($exists) {
                                    $count++;
                                }
                            } while ($exists);
                            
                            // Update record
                            $record->update([
                                'nomor_surat' => $nomor_surat,
                                'ttd_ketua' => $user->tanda_tangan ?? '',
                            ]);

                            try {
                                // Generate file surat
                                $namaLingkungan = $lingkungan ? $lingkungan->nama_lingkungan : '';
                                $namaLingkunganSlug = Str::slug($namaLingkungan);

                                $templatePath = 'templates/surat_keterangan_kematian.docx';
                                $namaSurat = $namaLingkunganSlug . '-' . now()->format('d-m-Y-h-m-s') . '-surat_keterangan_kematian.docx';
                                $outputPath = storage_path('app/public/' . $namaSurat);
                                
                                // Data untuk template
                                $data = [
                                    'nomor_surat' => $nomor_surat,
                                    'nama_ketua' => $record->ketuaLingkungan->user->name ?? '',
                                    'nama_lingkungan' => $namaLingkungan,
                                    'paroki' => $record->lingkungan->paroki ?? 'St. Stephanus Cilacap',
                                    'nama_lengkap' => $record->user->name ?? $record->nama_lengkap,
                                    'usia' => $record->usia,
                                    'nama_ortu' => $record->nama_ortu,
                                    'nama_pasangan' => $record->nama_pasangan,
                                    'tgl_kematian' => $record->tgl_kematian->locale('id')->translatedFormat('d F Y'),
                                    'tgl_pemakaman' => $record->tgl_pemakaman->locale('id')->translatedFormat('d F Y'),
                                    'tempat_pemakaman' => $record->tempat_pemakaman,
                                    'pelayanan_sakramen' => $record->pelayanan_sakramen,
                                    'sakramen' => $record->sakramen,
                                    'tempat_baptis' => $record->tempat_baptis,
                                    'no_baptis' => $record->no_baptis,
                                    'tgl_surat' => $record->tgl_surat->locale('id')->translatedFormat('d F Y'),
                                    'ttd_ketua' => $user->tanda_tangan ?? '',
                                ];

                                $generateSurat = (new SuratKematianGenerate)->generateFromTemplate(
                                    $templatePath,  
                                    $outputPath,
                                    $data,
                                    public_path($user->tanda_tangan)
                                );

                                // Update surat yang sudah ada
                                $surat = Surat::where('id', $record->surat_id)
                                            ->where('status', 'menunggu')
                                            ->first();

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
                                ->title('Surat Keterangan Kematian diterima')
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
            'index' => Pages\ListKeteranganKematians::route('/'),
            'create' => Pages\CreateKeteranganKematian::route('/create'),
            'edit' => Pages\EditKeteranganKematian::route('/{record}/edit'),
        ];
    }
}