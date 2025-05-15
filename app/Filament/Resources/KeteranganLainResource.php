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
use App\Models\KeteranganLain;
use App\Models\KetuaLingkungan;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Services\SuratLainGenerate;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\KeteranganLainResource\Pages;

class KeteranganLainResource extends Resource
{
    protected static ?string $model = KeteranganLain::class;

    protected static ?string $navigationGroup = 'Surat';

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
                                        $set('nama_lingkungan', $lingkungan->nama_lingkungan);
                                        $set('paroki', $lingkungan->paroki ?? 'St. Stephanus Cilacap');
                                    }
                                    
                                    if ($ketuaLingkungan) {
                                        $set('ketua_lingkungan_id', $ketuaLingkungan->id);
                                    }
                                }
                            }),
                        Forms\Components\Hidden::make('ketua_lingkungan_id'),
                        Forms\Components\Hidden::make('nama_lingkungan'),
                        Forms\Components\Hidden::make('nomor_surat'),
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
                                    $user = User::find($state);
                                    if ($user) {
                                        $set('nama_lengkap', $user->name);
                                        $set('akun_email', $user->email);
                                        $set('tempat_lahir', $user->tempat_lahir);
                                        $set('tgl_lahir', $user->tgl_lahir);
                                        $set('telepon', $user->telepon);
                                        
                                        if ($user->detailUser) {
                                            $set('alamat', $user->detailUser->alamat);
                                        }
                                    }
                                }
                            }),
                    ]),
                Fieldset::make('Data Pemohon')
                    ->schema([
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->label('Nama Lengkap')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('akun_email')
                            ->required()
                            ->label('Akun Email')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->required()
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tgl_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Forms\Components\TextInput::make('pekerjaan')
                            ->required()
                            ->label('Pekerjaan')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat')
                            ->required()
                            ->label('Alamat')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('telepon')
                            ->tel()
                            ->required()
                            ->label('No. Telepon/HP')
                            ->maxLength(255),
                        Forms\Components\Select::make('status_tinggal')
                            ->required()
                            ->label('Status Tempat Tinggal')
                            ->options([
                                'Sendiri' => 'Sendiri',
                                'Bersama Keluarga' => 'Bersama Keluarga',
                                'Bersama Saudara' => 'Bersama Saudara',
                                'Kos/Kontrak' => 'Kos/Kontrak',
                            ]),
                        Forms\Components\Textarea::make('keperluan')
                            ->required()
                            ->label('Keperluan')
                            ->columnSpanFull(),
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
                    ->label('Nama Pemohon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keperluan')
                    ->label('Keperluan')
                    ->limit(30),
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
                    ->action(function (KeteranganLain $record) {
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
                                $nomor_surat = sprintf('%04d/KL/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = KeteranganLain::where('nomor_surat', $nomor_surat)->exists();
                                $count = $exists ? $count + 1 : $count;
                            } while ($exists);
                            
                            $record->update([
                                'nomor_surat' => $nomor_surat,
                                'ttd_ketua' => $user->tanda_tangan,
                                'nama_ketua' => $user->name,
                            ]);
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'nomor_surat' => $nomor_surat,
                                    'status' => 'menunggu_paroki',
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat telah disetujui Ketua Lingkungan')
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
                            $namaSurat = "surat-keterangan-{$namaLingkungan}-{$record->id}.docx";
                            $outputPath = storage_path("app/public/surat/{$namaSurat}");
                            $templatePath = base_path('templates/surat_keterangan_lain.docx');

                            // Data untuk template
                            $data = [
                                'nomor_surat' => $record->nomor_surat,
                                'nama_lengkap' => $record->nama_lengkap,
                                'tempat_lahir' => $record->tempat_lahir,
                                'tgl_lahir' => $record->tgl_lahir?->format('d-m-Y'),
                                'pekerjaan' => $record->pekerjaan,
                                'alamat' => $record->alamat,
                                'telepon' => $record->telepon,
                                'status_tinggal' => $record->status_tinggal,
                                'keperluan' => $record->keperluan,
                                'nama_lingkungan' => $record->lingkungan->nama_lingkungan ?? '',
                                'paroki' => $record->lingkungan->paroki ?? 'St. Stephanus Cilacap',
                                'nama_ketua' => $record->nama_ketua,
                                'nama_pastor' => $user->name,
                                'tgl_surat' => $record->tgl_surat->format('d-m-Y'),
                            ];
                            
                            $generateSurat = (new SuratLainGenerate)->generateFromTemplate(
                                $templatePath,  
                                $outputPath,
                                $data,
                                'ketua',
                                'paroki'
                            );
                            
                            // Update surat terkait
                            if ($record->surat) {
                                $record->surat->update([
                                    'status' => 'selesai',
                                    'file_surat' => "surat/{$namaSurat}",
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Surat telah disetujui Pastor')
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
            'index' => Pages\ListKeteranganLains::route('/'),
            'create' => Pages\CreateKeteranganLain::route('/create'),
            'edit' => Pages\EditKeteranganLain::route('/{record}/edit'),
        ];
    }
}