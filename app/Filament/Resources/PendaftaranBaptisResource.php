<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use App\Models\PendaftaranBaptis;
use Illuminate\Support\Facades\Auth;
use App\Services\SuratBaptisGenerate;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\PendaftaranBaptisResource\Pages;
use App\Filament\Resources\PendaftaranBaptisResource\RelationManagers;

class PendaftaranBaptisResource extends Resource
{
    protected static ?string $model = PendaftaranBaptis::class;

    protected static ?string $navigationGroup = 'Surat';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Data Pendaftar')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_baptis')
                            ->required()
                            ->label('Nama Baptis')
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
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->required()
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nomor_telepon')
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
                                'Islam' => 'Islam',
                                'Hindu' => 'Hindu',
                                'Budha' => 'Budha',
                                'Protestan' => 'Protestan',
                            ]),
                        Forms\Components\Select::make('pendidikan_terakhir')
                            ->required()
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'Diploma/Sarjana' => 'Diploma/Sarjana',
                            ]),
                            Forms\Components\DatePicker::make('tanggal_mulai_belajar')
                                ->required()
                                ->label('Tanggal Mulai Pembelajaran'),
                            Forms\Components\TextInput::make('nama_wali_baptis')
                                ->required()
                                ->label('Nama Wali Baptis')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('alasan_masuk_katolik')
                                ->required()
                                ->label('Alasan Masuk Katolik')
                                ->columnSpanFull(),
                            Forms\Components\DatePicker::make('tanggal_baptis')
                                ->required(),       
                    ]),
                    Fieldset::make('Data Keluarga')
                        ->schema([
                            Forms\Components\TextInput::make('nama_ayah')
                                ->required()
                                ->label('Nama Ayah')
                                ->maxLength(255),
                            Forms\Components\Select::make('agama_ayah')
                                ->required()
                                ->label('Agama Ayah')
                                ->options([
                                    'Katolik' => 'Katolik',
                                    'Protestan' => 'Protestan',
                                    'Islam' => 'Islam',
                                    'Hindu' => 'Hindu',
                                    'Budha' => 'Budha',
                                ]),
                            Forms\Components\TextInput::make('nama_ibu')
                                ->required()
                                ->label('Nama Ibu')
                                ->maxLength(255),
                            Forms\Components\Select::make('agama_ibu')
                                ->required()
                                ->label('Agama Ibu')
                                ->options([
                                    'Katolik' => 'Katolik',
                                    'Protestan' => 'Protestan',
                                    'Islam' => 'Islam',
                                    'Hindu' => 'Hindu',
                                    'Budha' => 'Budha',
                                ]),
                            Forms\Components\TextInput::make('nama_keluarga_katolik_1')
                                ->maxLength(255)
                                ->label('Nama Keluarga 1'),
                            Forms\Components\Select::make('hubungan_keluarga_katolik_1')
                                ->label('Hubungan Keluarga 1')
                                ->options([
                                    'Saudara Kandung' => 'Saudara Kandung',
                                    'Pasangan' => 'Pasangan',
                                    'Sepupu' => 'Sepupu',
                                    'Wali' => 'Wali',
                                    'Kerabat Lainnya' => 'Kerabat Lainnya',
                                ]),
                            Forms\Components\TextInput::make('nama_keluarga_katolik_2')
                                ->maxLength(255)
                                ->label('Nama Keluarga 2'),
                            Forms\Components\Select::make('hubungan_keluarga_katolik_2')
                                ->label('Hubungan Keluarga 2')
                                ->options([
                                    'Saudara Kandung' => 'Saudara Kandung',
                                    'Pasangan' => 'Pasangan',
                                    'Sepupu' => 'Sepupu',
                                    'Wali' => 'Wali',
                                    'Kerabat Lainnya' => 'Kerabat Lainnya',
                                ]),
                            Forms\Components\Textarea::make('alamat_keluarga')
                                ->required()
                                ->label('Alamat Lengkap')
                                ->columnSpanFull(),
                            SignaturePad::make('tanda_tangan_ortu')
                                ->label('Tanda Tangan Orang Tua'),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::where('id', Auth::user()->id)->first();
                // dd($user);
                // Jika user memiliki role paroki, tampilkan semua data
                if ($user->hasRole('paroki')) {
                    return $query->whereNotNull('nomor_surat')
                                ->whereNotNull('tanda_tangan_ketua');
                }
                // Jika bukan role paroki, filter berdasarkan lingkungan
                return $query->where('nama_lingkungan', $user->lingkungan?->nama_lingkungan);
            })
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lingkungan')
                    ->label('Lingkungan / Stasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_baptis')
                    ->label('Nama Baptis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin'),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_telepon')
                    ->label('No. Telp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_wali_baptis')
                    ->label('Nama Wali Baptis')
                    ->searchable(),
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
                    ->label(function($record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        if ($user->hasRole('ketua_lingkungan')) {
                            return $record->nomor_surat === null ? 'Accept' : 'Done';
                        } elseif ($user->hasRole('paroki')) {
                            return $record->tanda_tangan_pastor === null ? 'Accept' : 'Done';
                        }
                        return 'Accept';
                    })
                    ->color(function($record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        if ($user->hasRole('ketua_lingkungan')) {
                            return $record->nomor_surat === null ? 'warning' : 'success';
                        } elseif ($user->hasRole('paroki')) {
                            return $record->tanda_tangan_pastor === null ? 'warning' : 'success';
                        }
                        return 'warning';
                    })
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->disabled(function($record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        // Jika user adalah ketua_lingkungan
                        if ($user->hasRole('ketua_lingkungan')) {
                            // Disable tombol jika sudah memiliki nomor surat
                            return $record->nomor_surat !== null;
                        } 
                        // Jika user adalah paroki
                        elseif ($user->hasRole('paroki')) {
                            // Pastikan nomor_surat sudah ada (sudah disetujui ketua_lingkungan)
                            // dan tanda_tangan_pastor belum ada (belum disetujui paroki)
                            return $record->nomor_surat === null || $record->tanda_tangan_pastor !== null;
                        }
                        
                        return true; // Default disabled untuk peran lain
                    })
                    ->action(function (PendaftaranBaptis $record) {
                        $user = User::where('id', Auth::user()->id)->first();
                        
                        // Jika user role ketua lingkungan 
                        if ($user->hasRole('ketua_lingkungan')) {  
                            // Generate nomor surat
                            $tahun = Carbon::now()->format('Y');
                            $bulan = Carbon::now()->format('m');
                            // Ambil kode dari user yang login
                            $kode = Auth::user()->lingkungan->kode; // Mengasumsikan user memiliki relasi ke model lingkungan dan ada field kode
                            // Inisialisasi count
                            $count = 1;
                            // Mencari nomor yang belum ada
                            do {
                                $nomor_surat = sprintf('%04d/PB/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = PendaftaranBaptis::where('nomor_surat', $nomor_surat)->exists();
                                if ($exists) {
                                    $count++; // Jika nomor sudah ada, tingkatkan count
                                }
                            } while ($exists); // Setelah keluar dari loop, $nomor_surat adalah unik
                            
                            // Dapatkan tanda tangan ketua lingkungan
                            $tanda_tangan_ketua = $user->tanda_tangan ?? '';
                            
                            // Update record dengan nomor surat dan tanda tangan ketua
                            $record->update([
                                'nomor_surat' => $nomor_surat,
                                'tanda_tangan_ketua' => $tanda_tangan_ketua,
                            ]);
                            
                            \Filament\Notifications\Notification::make('approval')
                                ->title('Surat Pendaftaran Baptis diterima')
                                ->success()
                                ->send();
                        } 
                        elseif ($user->hasRole('paroki')) {  // Jika user role paroki
                            // Dapatkan tanda tangan pastor (user paroki)
                            $tanda_tangan_pastor = $user->tanda_tangan ?? '';
                            
                            // Update record dengan tanda tangan pastor saja
                            $record->update([
                                'tanda_tangan_pastor' => $tanda_tangan_pastor,
                            ]);

                            $templatePath = 'templates/surat_pendaftaran_baptis.docx';
                            $namaSurat = $record->nama_lingkungan .'-'.$record->tanggal_daftar.'-surat_pendaftaran_baptis.docx';
                            $outputPath = storage_path('app/public/'.$namaSurat);
                            $generateSurat = (new SuratBaptisGenerate)->generateFromTemplate(
                                $templatePath,  
                                $outputPath,
                                $record->toArray(),
                                'ortu',
                                'ketua',
                                'paroki'
                            );

                            Surat::create([
                                'kode_nomor_surat' => $record->nomor_surat,
                                'perihal_surat' => 'Pendaftaran Baptis',
                                'atas_nama' => $record->nama_lengkap,
                                'nama_lingkungan' => $record->nama_lingkungan,
                                'status' => 'Selesai',
                                'file_surat' => $namaSurat,
                            ]);
                            
                            \Filament\Notifications\Notification::make('approval')
                                ->title('Surat Pendaftaran Baptis diterima')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
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
