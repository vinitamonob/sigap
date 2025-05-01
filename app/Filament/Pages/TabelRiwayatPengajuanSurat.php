<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Models\Surat; 

class TabelRiwayatPengajuanSurat extends Page implements HasTable
{
    use InteractsWithTable;

    use HasPageShield;

    protected static ?string $navigationGroup = 'Status Pengajuan';

    protected static ?string $navigationLabel = 'Riwayat Pengajuan Surat';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.tabel-riwayat-pengajuan-surat';

    public function table(Table $table): Table
    {
        return $table
            ->query(Surat::query())
            ->columns([
                TextColumn::make('kode_nomor_surat')
                    ->label('Kode Surat')
                    ->searchable(),
                TextColumn::make('perihal_surat')
                    ->label('Nama Surat')
                    ->searchable(),
                TextColumn::make('atas_nama')
                    ->label('Atas Nama')
                    ->searchable(),
                TextColumn::make('nama_lingkungan')
                    ->label('Lingkungan / Stasi')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                TextColumn::make('file_surat')
                    ->label('File Surat')
                    ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    protected function getTableQuery()
    {
        return Surat::query();
    }
}
