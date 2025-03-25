<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PendaftaranKanonikPerkawinan;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource\Pages;
use App\Filament\Resources\PendaftaranKanonikPerkawinanResource\RelationManagers;
use PhpParser\Node\Stmt\Label;

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
                            ->label('Nama lengkap CalonIstri')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_lahir_istri')
                            ->required()
                            ->label('Tempat Lahir Istri')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir_istri')
                            ->required(),
                        Forms\Components\Textarea::make('alamat_sekarang_istri')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('alamat_setelah_menikah_istri')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('telepon_istri')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pekerjaan_istri')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pendidikan_terakhir_istri')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('agama_istri')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tempat_baptis_istri')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_baptis_istri'),
                        SignaturePad::make('tanda_tangan_calon_istri'),
                        Fieldset::make('Data Orang Tua')
                            ->schema([
                                Forms\Components\TextInput::make('nama_ayah_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('agama_ayah_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('pekerjaan_ayah_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat_ayah_istri')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('nama_ibu_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('agama_ibu_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('pekerjaan_ibu_istri')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat_ibu_istri')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),   
                            Fieldset::make('Data Lingkungan')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_ketua_istri')
                                        ->required()
                                        ->label('Nama Ketua Lingkungan')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('nama_lingkungan_istri')
                                        ->required()
                                        ->label('Nama Lingkungan / Stasi')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('wilayah_istri')
                                        ->required()
                                        ->label('Wilayah')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('paroki_istri')
                                        ->required()
                                        ->label('Paroki')
                                        ->maxLength(255),
                                    SignaturePad::make('tanda_tangan_ketua_istri')
                                        ->label('Tanda Tangan Ketua Lingkungan'),
                                ])                          
                    ]),
                    Fieldset::make('Data Calon Suami')
                        ->schema([
                            Forms\Components\TextInput::make('nama_suami')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_lahir_suami')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_lahir_suami')
                                ->required(),
                            Forms\Components\Textarea::make('alamat_sekarang_suami')
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('alamat_setelah_menikah_suami')
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('telepon_suami')
                                ->tel()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pekerjaan_suami')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pendidikan_terakhir_suami')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('agama_suami')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_baptis_suami')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_baptis_suami'),
                            SignaturePad::make('tanda_tangan_calon_suami'),
                            Fieldset::make('Data Orang Tua')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_ayah_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('agama_ayah_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('pekerjaan_ayah_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('alamat_ayah_suami')
                                        ->required()
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('nama_ibu_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('agama_ibu_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('pekerjaan_ibu_suami')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('alamat_ibu_suami')
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                                Fieldset::make('Data Lingkungan')
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_ketua_suami')
                                            ->required()
                                            ->label('Nama Ketua Lingkungan')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('nama_lingkungan_suami')
                                            ->required()
                                            ->label('Nama Lingkungan / Stasi')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('wilayah_suami')
                                            ->required()
                                            ->label('Wilayah')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('paroki_suami')
                                            ->required()
                                            ->label('Paroki')
                                            ->maxLength(255),
                                        SignaturePad::make('tanda_tangan_ketua_suami')
                                            ->label('Tanda Tangan Ketua Lingkungan'),
                                    ])
                        ]),
                        Fieldset::make('Data Perkawinan')
                            ->schema([
                                Fieldset::make('Label')
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal_daftar')
                                            ->required()
                                            ->default(now())
                                            ->readOnly(),
                                    ]),
                                Forms\Components\TextInput::make('lokasi_gereja')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('tanggal_pernikahan')
                                    ->required(),
                                Forms\Components\TimePicker::make('waktu_pernikahan')
                                    ->required(),
                            ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_istri')
                    ->label('Nama Calon Istri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_lahir_istri')
                    ->label('Tempat Lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir_istri')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telepon_istri')
                    ->label('No. Telp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paroki_istri')
                    ->label('Paroki Calon Istri')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_suami')
                    ->label('Nama Calon Suami')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_lahir_suami')
                    ->label('Tempat Lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir_suami')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telepon_suami')
                    ->label('No. Telp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paroki_suami')
                    ->label('Paroki Calon Suami')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lokasi_gereja')
                    ->label('Lokasi Pemberkatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pernikahan')
                    ->label('Tanggal Pernikahan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_pernikahan')
                    ->label('Waktu Pernikahan'),
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
            'index' => Pages\ListPendaftaranKanonikPerkawinans::route('/'),
            'create' => Pages\CreatePendaftaranKanonikPerkawinan::route('/create'),
            'edit' => Pages\EditPendaftaranKanonikPerkawinan::route('/{record}/edit'),
        ];
    }
}
