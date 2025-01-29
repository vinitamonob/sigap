<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\KeteranganKematian;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\KeteranganKematianResource\Pages;
use App\Filament\Resources\KeteranganKematianResource\RelationManagers;

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
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_ketua')
                            ->required()
                            ->default(Auth::user()->name)
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ketua_lingkungan')
                            ->required()
                            ->label('Ketua Lingkungan / Stasi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('paroki')
                            ->required()
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->required()
                            ->default(now())
                            ->readOnly(),
                        // Forms\Components\TextInput::make('tanda_tangan')
                        //     ->required()
                        //     ->readOnly()
                        //     ->maxLength(255),
                        SignaturePad::make('tanda_tangan')
                    ]),
                    Fieldset::make('Data Kematian')
                        ->schema([
                            Forms\Components\TextInput::make('nama_lengkap')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('usia')
                                ->required()
                                ->numeric(),
                            Forms\Components\TextInput::make('nama_orang_tua')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nama_pasangan')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('tanggal_kematian')
                                ->required(),
                            Forms\Components\DatePicker::make('tanggal_pemakaman')
                                ->required(),
                            Forms\Components\TextInput::make('tempat_pemakaman')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('pelayan_sakramen')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('sakramen_yang_diberikan')
                                ->required()
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
                Tables\Columns\TextColumn::make('usia')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_orang_tua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pasangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_kematian')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pemakaman')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tempat_pemakaman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pelayan_sakramen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sakramen_yang_diberikan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_no_buku_baptis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanda_tangan')
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
            'index' => Pages\ListKeteranganKematians::route('/'),
            'create' => Pages\CreateKeteranganKematian::route('/create'),
            'edit' => Pages\EditKeteranganKematian::route('/{record}/edit'),
        ];
    }
}
