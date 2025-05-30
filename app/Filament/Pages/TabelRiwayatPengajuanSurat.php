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

    protected static ?string $navigationGroup = 'Riwayat';
    protected static ?string $navigationLabel = 'Pengajuan Surat';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.tabel-riwayat-pengajuan-surat';

    public function table(Table $table): Table
    {
        return $table
            ->query(Surat::query())
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::where('id', Auth::user()->id)->first();
                if ($user->hasRole('super_admin')) {
                    return $query;
                }
                if ($user->hasRole('umat')) {
                    return $query->where('user_id', $user->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable(),
                TextColumn::make('perihal')
                    ->label('Perihal Surat')
                    ->searchable(),
                TextColumn::make('lingkungan.nama_lingkungan')
                    ->label('Lingkungan / Stasi')
                    ->searchable(),
                TextColumn::make('tgl_surat')
                    ->label('Tanggal Surat')
                    ->dateTime('d-m-Y') 
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'menunggu_paroki' => 'warning',
                        'diterima' => 'success',
                        default => 'gray',
                    })
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