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
use App\Models\Lingkungan;
use App\Models\User;
use Carbon\Carbon;

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
                        Forms\Components\TextInput::make('nama_ketua')
                            ->required()
                            ->label('Nama Ketua Lingkungan')
                            ->default(Auth::user()->name)
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_lingkungan')
                            ->required()
                            ->label('Ketua Lingkungan / Stasi')
                            ->default(fn () => Lingkungan::where('user_id', Auth::id())->first()->nama_lingkungan)
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('paroki')
                            ->required()
                            ->label('Paroki')
                            ->default('St. Stephanus Cilacap')
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->required()
                            ->label('Tanggal Surat')
                            ->default(Carbon::now())
                            ->readOnly(),
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
                                ->numeric(),
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
        // ->modifyQueryUsing(fn (Builder $query) => $query->where('ketua_lingkungan', Auth::user()->lingkungan->nama_lingkungan))
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
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
