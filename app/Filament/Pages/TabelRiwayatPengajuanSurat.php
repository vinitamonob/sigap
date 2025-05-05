<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Surat; 
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

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
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::where('id', Auth::user()->id)->first(); // Ambil user yang sedang login
                // Tampilkan semua data jika user adalah super_admin
                if ($user->hasRole('super_admin')) {
                    return $query;
                }
                // Jika user adalah umat, tampilkan data berdasarkan user_id
                if ($user->hasRole('umat')) {
                    return $query->where('user_id', $user->id);
                }
                // Jika tidak ada role yang sesuai, tampilkan semua data (atau bisa disesuaikan)
                return $query;
            })
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
