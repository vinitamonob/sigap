<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PendaftaranBaptis;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\PendaftaranBaptisResource\Pages;
use App\Filament\Resources\PendaftaranBaptisResource\RelationManagers;

class PendaftaranBaptisResource extends Resource
{
    protected static ?string $model = PendaftaranBaptis::class;

    protected static ?string $navigationGroup = 'Formulir';

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
                                'Protestan' => 'Protestan'
                            ]),
                        Forms\Components\Radio::make('pendidikan_terakhir')
                            ->required()
                            ->label('Pendidikan Terakhir')
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP'
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
                            Forms\Components\TextInput::make('agama_ayah')
                                ->required()
                                ->label('Agama Ayah')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nama_ibu')
                                ->required()
                                ->label('Nama Ibu')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('agama_ibu')
                                ->required()
                                ->label('Agama Ibu')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nama_keluarga_katolik_1')
                                ->maxLength(255)
                                ->label('Nama Keluarga 1'),
                            Forms\Components\TextInput::make('hubungan_keluarga_katolik_1')
                                ->maxLength(255)
                                ->label('Hubungan Keluarga 1'),
                            Forms\Components\TextInput::make('nama_keluarga_katolik_2')
                                ->maxLength(255)
                                ->label('Nama Keluarga 2'),
                            Forms\Components\TextInput::make('hubungan_keluarga_katolik_2')
                                ->maxLength(255)
                                ->label('Hubungan Keluarga 2'),
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
            ->columns([
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
                Tables\Columns\TextColumn::make('tanggal_mulai_belajar')
                    ->label('Tanggal Pembelajaran')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_wali_baptis')
                    ->label('Nama Wali Baptis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_baptis')
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
