<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LingkunganResource\Pages;
use App\Filament\Resources\LingkunganResource\RelationManagers;
use App\Models\Lingkungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LingkunganResource extends Resource
{
    protected static ?string $model = Lingkungan::class;

    protected static ?string $navigationGroup = 'Data';

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
                Forms\Components\TextInput::make('user.name')
                    ->required()
                    ->label('Nama Ketua Lingkungan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user.email')
                    ->email()
                    ->label('Email')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lingkungan')
                    ->label('Nama Lingkungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('telepon')
                    ->label('No. Telp')
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
