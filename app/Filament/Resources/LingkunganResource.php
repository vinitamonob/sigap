<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\LingkunganResource\Pages;

class LingkunganResource extends Resource
{
    protected static ?string $model = Lingkungan::class;

    protected static ?string $navigationGroup = 'Kelola Data';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode')
                    ->required()
                    ->label('Kode Surat')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_lingkungan')
                    ->required()
                    ->label('Nama Lingkungan')
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->label('Ketua Lingkungan')
                    ->options(User::whereHas('roles', function ($query) {$query->where('name', 'ketua_lingkungan');})->whereDoesntHave('lingkungan')->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Ketua Lingkungan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lingkungan')
                    ->label('Nama Lingkungan / Stasi')
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
            'index' => Pages\ListLingkungans::route('/'),
            'create' => Pages\CreateLingkungan::route('/create'),
            'edit' => Pages\EditLingkungan::route('/{record}/edit'),
        ];
    }
}
