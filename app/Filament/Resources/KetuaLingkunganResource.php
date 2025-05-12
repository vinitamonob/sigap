<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Lingkungan;
use Filament\Tables\Table;
use App\Models\KetuaLingkungan;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KetuaLingkunganResource\Pages;
use App\Filament\Resources\KetuaLingkunganResource\RelationManagers;

class KetuaLingkunganResource extends Resource
{
    protected static ?string $model = KetuaLingkungan::class;

    protected static ?string $navigationGroup = 'Kelola Data';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lingkungan_id')
                    ->required()
                    ->label('Nama Lingkungan / Stasi')
                    ->options(Lingkungan::pluck('nama_lingkungan', 'id')->toArray())
                    ->searchable(),
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->label('Ketua Lingkungan')
                    ->options(User::role('ketua_lingkungan')->pluck('name', 'id')->toArray())
                    ->searchable(),
                Forms\Components\DatePicker::make('mulai_jabatan')
                    ->label('Mulai Jabatan'),
                Forms\Components\DatePicker::make('akhir_jabatan')
                    ->label('Akhir Jabatan'),
                Forms\Components\Toggle::make('aktif')
                    ->required()
                    ->label('Status Jabatan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Ketua Lingkungan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lingkungan.nama_lingkungan')
                    ->label('Nama Lingkungan / Stasi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mulai_jabatan')
                    ->label('Mulai Jabatan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('akhir_jabatan')
                    ->label('Akhir Jabatan')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status Jabatan')
                    ->boolean(),
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
            'index' => Pages\ListKetuaLingkungans::route('/'),
            'create' => Pages\CreateKetuaLingkungan::route('/create'),
            'edit' => Pages\EditKetuaLingkungan::route('/{record}/edit'),
        ];
    }
}
