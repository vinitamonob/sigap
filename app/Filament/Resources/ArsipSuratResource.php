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
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ArsipSuratResource\Pages;
use App\Filament\Resources\ArsipSuratResource\RelationManagers;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ArsipSuratResource extends Resource
{
    protected static ?string $model = Surat::class;
    protected static ?string $modelLabel = 'Arsip Surat';
    protected static ?string $navigationGroup = 'Kelola Data';
    protected static ?string $navigationLabel = 'Arsip Surat';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pilih Umat')
                    ->options(function () {
                        return User::with('detailUser')->get()
                            ->mapWithKeys(function ($user) {
                                return [$user->id => $user->name];
                            });
                    })
                    ->searchable(),
                Forms\Components\Select::make('lingkungan_id')
                    ->required()
                    ->label('Nama Lingkungan/Stasi')
                    ->options(Lingkungan::pluck('nama_lingkungan', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('jenis_surat')
                    ->required()
                    ->label('Jenis Surat')
                    ->options([
                        'keterangan_kematian' => 'Keterangan Kematian',
                        'keterangan_lain' => 'Keterangan Lain',
                        'pendaftaran_baptis' => 'Pendaftaran Baptis',
                        'pendaftaran_perkawinan' => 'Pendaftaran Kanonik & Perkawinan',
                    ])
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $perihalMap = [
                                'keterangan_kematian' => 'Keterangan Kematian',
                                'keterangan_lain' => 'Keterangan Lain',
                                'pendaftaran_baptis' => 'Pendaftaran Baptis',
                                'pendaftaran_perkawinan' => 'Pendaftaran Kanonik & Perkawinan',
                            ];
                            
                            if (array_key_exists($state, $perihalMap)) {
                                $set('perihal', $perihalMap[$state]);
                            }
                        }
                }),
                Forms\Components\TextInput::make('perihal')
                    ->required()
                    ->label('Perihal'),
                Forms\Components\TextInput::make('nomor_surat')
                    ->required()
                    ->label('Nomor Surat')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tgl_surat')
                    ->required()
                    ->label('Tanggal Surat'),
                Forms\Components\Hidden::make('status')
                    ->default('selesai'),
                FileUpload::make('file_surat')
                    ->required() 
                    ->label('File Surat')
                    // Tentukan jenis file yang diizinkan
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    // Simpan file dengan ekstensi aslinya
                    ->getUploadedFileNameForStorageUsing(
                        function (Forms\Get $get, TemporaryUploadedFile $file): string {
                            $jenisSurat = $get('jenis_surat');
                            $extension = $file->getClientOriginalExtension(); // Dapatkan ekstensi asli file
                            return Carbon::now()->format('d-m-Y-H-i-s') . '-' . $jenisSurat . '.' . $extension;
                        }
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lingkungan.nama_lingkungan')
                    ->label('Lingkungan / Stasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Atas Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_surat')
                    ->label('Tanggal Surat')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        default => 'success',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_surat')
                    ->label('File Surat')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            $url = Storage::url($state);
                            return '<a href="' . $url . '" target="_blank" class="underline text-sm text-primary-600 hover:text-primary-500">Download</a>';
                        }
                        return '-';
                    })
                    ->html(),
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
            'index' => Pages\ListArsipSurats::route('/'),
            'create' => Pages\CreateArsipSurat::route('/create'),
            'edit' => Pages\EditArsipSurat::route('/{record}/edit'),
        ];
    }
}