<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\KeteranganKematian;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\KeteranganKematianResource\Pages;
use App\Filament\Resources\KeteranganKematianResource\RelationManagers;
use App\Models\Surat;
use App\Services\SuratKematianGenerate;

class KeteranganKematianResource extends Resource
{
    protected static ?string $model = KeteranganKematian::class;

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
                            ->default(Carbon::now())
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
                    Fieldset::make('Data Kematian')
                        ->schema([
                            Forms\Components\TextInput::make('nama_lengkap')
                                ->required()
                                ->label('Nama Lengkap')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('usia')
                                ->required()
                                ->label('Usia')
                                ->numeric()
                                ->minValue(0),
                            Forms\Components\TextInput::make('nama_orang_tua')
                                ->required()
                                ->label('Nama Orang Tua')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nama_pasangan')
                                ->required()
                                ->label('Nama Pasangan')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_kematian')
                                ->required()
                                ->label('Tanggal Kematian'),
                            Forms\Components\DatePicker::make('tanggal_pemakaman')
                                ->required()
                                ->label('Tanggal Pemakaman'),
                            Forms\Components\TextInput::make('tempat_pemakaman')
                                ->required()
                                ->label('Tempat Pemakaman')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pelayan_sakramen')
                                ->required()
                                ->label('Pelayanan Sakramen')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('sakramen_yang_diberikan')
                                ->required()
                                ->label('Sakramen yang Diberikan')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_no_buku_baptis')
                                ->required()
                                ->label('Tempat & No. Buku Baptis')
                                ->maxLength(255),
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::where('id', Auth::user()->id)->first();
                if ($user->lingkungan && $user->lingkungan->nama_lingkungan) {
                    return $query->where('nama_lingkungan', $user->lingkungan->nama_lingkungan);
                } else {
                    // Jika user tidak memiliki lingkungan atau nama_lingkungan null,
                    // tampilkan semua data (tidak menerapkan filter apapun)
                    return $query;
                }
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
                Tables\Columns\TextColumn::make('usia')
                    ->label('Usia')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kematian')
                    ->label('Tanggal Kematian')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pemakaman')
                    ->label('Tanggal Pemakaman')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tempat_pemakaman')
                    ->label('Tempat Pemakaman')
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
                    ->label(fn($record) => $record->nomor_surat === null ? 'Accept' : 'Done')
                    ->color(fn($record) => $record->nomor_surat === null ? 'warning' : 'success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->disabled(fn($record) => $record->nomor_surat !== null)
                    ->action(function (KeteranganKematian $record) {
                        // Generate nomor surat
                        $tahun = Carbon::now()->format('Y');
                        $bulan = Carbon::now()->format('m');
                        // Ambil kode dari user yang login
                        $kode = Auth::user()->lingkungan->kode; // Mengasumsikan user memiliki relasi ke model lingkungan dan ada field kode
                        // Inisialisasi count
                        $count = 1;
                        // Mencari nomor yang belum ada
                        do {
                            $nomor_surat = sprintf('%04d/KK/%s/%s/%s', $count, $kode, $bulan, $tahun);
                            $exists = KeteranganKematian::where('nomor_surat', $nomor_surat)->exists();
                            if ($exists) {
                                $count++; // Jika nomor sudah ada, tingkatkan count
                            }
                        } while ($exists); // Setelah keluar dari loop, $nomor_surat adalah unik
                        
                        // Dapatkan tanda tangan ketua lingkungan yang login (jika ada)
                        $user = Auth::user();
                        $tanda_tangan = $user->tanda_tangan ?? '';
                        
                        // Update record
                        $record->update([
                            'nomor_surat' => $nomor_surat,
                            'pelayan_sakramen' => 'Minyak Suci',
                            'sakramen_yang_diberikan' => 'Perminyakan',
                            'tanda_tangan_ketua' => $tanda_tangan,
                        ]);

                        $templatePath = 'templates/surat_keterangan_kematian.docx';
                        $namaSurat = $record->nama_lingkungan .'-'.$record->tanggal_surat.'-surat_keteragan_kematian.docx';
                        $outputPath = storage_path('app/public/'.$namaSurat);
                        $generateSurat = (new SuratKematianGenerate)->generateFromTemplate(
                            $templatePath,  
                            $outputPath,
                            $record->toArray(),
                            'ketua'
                        );

                        Surat::create([
                            'kode_nomor_surat' => $record->nomor_surat,
                            'perihal_surat' => 'Keterangan Kematian',
                            'atas_nama' => $record->nama_lengkap,
                            'nama_lingkungan' => $record->nama_lingkungan,
                            'status' => 'Selesai',
                            'file_surat' => $namaSurat,
                        ]);
                        
                        \Filament\Notifications\Notification::make('approval')
                            ->title('Surat Keterangan Kematian diterima')
                            ->success()
                            ->send();
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
            'index' => Pages\ListKeteranganKematians::route('/'),
            'create' => Pages\CreateKeteranganKematian::route('/create'),
            'edit' => Pages\EditKeteranganKematian::route('/{record}/edit'),
        ];
    }
}
