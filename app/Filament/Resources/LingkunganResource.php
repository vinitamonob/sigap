<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LingkunganResource\Pages;
use App\Models\Lingkungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LingkunganResource extends Resource
{
    protected static ?string $model = Lingkungan::class;
    protected static ?string $navigationGroup = 'Kelola Data';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_lingkungan')
                    ->required()
                    ->label('Nama Lingkungan / Stasi')
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode')
                    ->label('Kode Lingkungan')
                    ->maxLength(255),
                Forms\Components\Select::make('wilayah')
                    ->label('Wilayah')
                    ->options([
                        'Cilacap Kota' => 'Cilcap Kota',
                        'Jeruk Legi' => 'Jeruk Legi',
                        'Kesugihan' => 'Kesugihan',
                        'Kawunganten' => 'Kawunganten',
                        'Ujungalang' => 'Ujungalang',
                    ]),
                Forms\Components\TextInput::make('paroki')
                    ->label('Paroki')
                    ->default('St. Stephanus Cilacap')
                    ->readOnly()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lingkungan')
                    ->searchable()
                    ->label('Lingkungan / Stasi'),
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->label('Kode Lingkungan'),
                Tables\Columns\TextColumn::make('wilayah')
                    ->searchable()
                    ->label('Wilayah'),
                Tables\Columns\TextColumn::make('paroki')
                    ->searchable()
                    ->label('Paroki'),
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
            'index' => Pages\ListLingkungans::route('/'),
            'create' => Pages\CreateLingkungan::route('/create'),
            'edit' => Pages\EditLingkungan::route('/{record}/edit'),
        ];
    }
}
