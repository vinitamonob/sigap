<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use App\Models\KeteranganLain;
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
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_ketua')
                            ->required()
                            ->default(Auth::user()->name)
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\Select::make('ketua_lingkungan')
                            ->required()
                            ->label('Ketua Lingkungan / Stasi')
                            ->options(Lingkungan::all()->pluck('nama_lingkungan', 'nama_lingkungan'))
                            ->searchable(),
                        Forms\Components\TextInput::make('paroki')
                            ->required()
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->required()
                            ->default(now())
                            ->readOnly(),
                    ]),
                    Fieldset::make('Data Keperluan')
                        ->schema([
                            Forms\Components\TextInput::make('nama_lengkap')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tempat_lahir')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_lahir')
                                ->required(),
                            Forms\Components\TextInput::make('jabatan_pekerjaan')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('alamat')
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('telepon_rumah')
                                ->tel()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('telepon_kantor')
                                ->tel()
                                ->maxLength(255),
                            Forms\Components\Select::make('status_tinggal')
                                ->required()
                                ->options([
                                    'Sendiri' => 'Sendiri',
                                    'Bersama Keluarga' => 'Bersama Keluarga',
                                    'Bersama Saudara' => 'Bersama Saudara',
                                    'Kos/Kontrak' => 'Kos/Kontrak',
                                ]),
                            Forms\Components\Textarea::make('keperluan')
                                ->required()
                                ->columnSpanFull(),
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ketua_lingkungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paroki')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jabatan_pekerjaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon_rumah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon_kantor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_tinggal'),
                Tables\Columns\TextColumn::make('tanda_tangan_ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_ttd_pastor')
                    ->label('Tanda tangan pastor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_surat')
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
            'index' => Pages\ListKeteranganLains::route('/'),
            'create' => Pages\CreateKeteranganLain::route('/create'),
            'edit' => Pages\EditKeteranganLain::route('/{record}/edit'),
        ];
    }
}
