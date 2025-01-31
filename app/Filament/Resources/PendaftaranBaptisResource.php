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
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_baptis')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Radio::make('jenis_kelamin')
                            ->required()
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'Pria' => 'Pria',
                                'Wanita' => 'Wanita'
                            ]),
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->required(),
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nomor_telepon')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Radio::make('agama_asal')
                            ->required()
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
                            ->inline()
                            ->inlineLabel(false)
                            ->options([
                                'TK' => 'TK',
                                'SD' => 'SD',
                                'SMP' => 'SMP'
                            ]),
                            Fieldset::make('Data Keluarga')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_ayah')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('agama_ayah')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('nama_ibu')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('agama_ibu')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('nama_keluarga_katolik_1')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('hubungan_keluarga_katolik_1')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('nama_keluarga_katolik_2')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('hubungan_keluarga_katolik_2')
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('alamat_keluarga')
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                        Forms\Components\DatePicker::make('tanggal_mulai_belajar')
                            ->required(),
                        Forms\Components\TextInput::make('nama_wali_baptis')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alasan_masuk_katolik')
                            ->required()
                            ->columnSpanFull(),
                        SignaturePad::make('tanda_tangan_ortu')
                        // Forms\Components\DatePicker::make('tanggal_baptis')
                        //     ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_baptis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin'),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama_asal'),
                Tables\Columns\TextColumn::make('pendidikan_terakhir'),
                Tables\Columns\TextColumn::make('nama_ayah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama_ayah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_ibu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama_ibu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_keluarga_katolik_1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hubungan_keluarga_katolik_1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_keluarga_katolik_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hubungan_keluarga_katolik_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_mulai_belajar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_wali_baptis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanda_tangan_ortu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanda_tangan_ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_ttd_pastor')
                    ->label('Tanda tangan pastor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_baptis')
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
