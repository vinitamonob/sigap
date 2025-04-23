<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use App\Models\KeteranganLain;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KeteranganLainResource\Pages;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\KeteranganLainResource\RelationManagers;

class KeteranganLainResource extends Resource
{
    protected static ?string $model = KeteranganLain::class;

    protected static ?string $navigationGroup = 'Surat';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Label')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_surat')
                            ->required()
                            ->label('Nomor Surat')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(now())
                            ->readOnly(),
                        Forms\Components\Select::make('nama_lingkungan')
                            ->required()
                            ->label('Nama Lingkungan / Stasi')
                            ->options(Lingkungan::pluck('nama_lingkungan', 'nama_lingkungan')->toArray())
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $lingkungan = Lingkungan::where('nama_lingkungan', $state)->first();
                                    if ($lingkungan && $lingkungan->user) {
                                        $set('nama_ketua', $lingkungan->user->name);
                                        $set('user_id', $lingkungan->user_id);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('nama_ketua')
                            ->required()
                            ->label('Nama Ketua Lingkungan')
                            ->readOnly(),
                        Forms\Components\TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                    ]),
                    Fieldset::make('Data Keperluan')
                        ->schema([
                            Forms\Components\TextInput::make('nama_lengkap')
                                ->required()
                                ->label('Nama Lengkap')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_lahir')
                                ->required()
                                ->label('Tempat Lahir')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_lahir')
                                ->required()
                                ->label('Tanggal Lahir'),
                            Forms\Components\TextInput::make('jabatan_pekerjaan')
                                ->required()
                                ->label('Jabatan Pekerjaan')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('alamat')
                                ->required()
                                ->label('Alamat')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('telepon_rumah')
                                ->tel()
                                ->label('No. Telepon Rumah')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('telepon_kantor')
                                ->tel()
                                ->label('No. Telepon Kantor')
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
                                ->label('Perihal / Keperluan')
                                ->columnSpanFull(),
                        ])
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
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
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
                    ->action(function (KeteranganLain $record) {
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
                                $nomor_surat = sprintf('%04d/KL/%s/%s/%s', $count, $kode, $bulan, $tahun);
                                $exists = KeteranganLain::where('nomor_surat', $nomor_surat)->exists();
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
                                ->title('Surat Keterangan Lain diterima')
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
                            
                            \Filament\Notifications\Notification::make('approval')
                                ->title('Surat Keterangan Lain diterima')
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
            'index' => Pages\ListKeteranganLains::route('/'),
            'create' => Pages\CreateKeteranganLain::route('/create'),
            'edit' => Pages\EditKeteranganLain::route('/{record}/edit'),
        ];
    }
}
