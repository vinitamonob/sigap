<?php

namespace App\Filament\Widgets;

use App\Models\Surat;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsDashboard extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
    
    protected static ?string $cacheKey = 'stats-dashboard-data';

    protected function getHeading(): string
    {
        $monthName = now()->translatedFormat('F'); // Nama bulan dalam bahasa Indonesia
        $year = now()->year; // Tahun sekarang
        return "Pengajuan Surat Bulan $monthName $year";
    }

    protected function getStats(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get current user with role information
        $user = User::where('id', Auth::id())->first();
        
        // Ambil data statistik per jenis surat
        $stats = [];
        $jenisSuratLabels = [
            'keterangan_kematian' => 'Keterangan Kematian',
            'keterangan_lain' => 'Keterangan Lain',
            'pendaftaran_baptis' => 'Pendaftaran Baptis',
            'pendaftaran_perkawinan' => 'Pendaftaran Perkawinan'
        ];

        foreach ($jenisSuratLabels as $jenis => $label) {
            // Base query for current month/year
            $query = Surat::where('jenis_surat', $jenis)
                ->whereMonth('tgl_surat', $currentMonth)
                ->whereYear('tgl_surat', $currentYear);
            
            // Apply role-based filtering
            if ($user->hasRole('ketua_lingkungan')) {
                $query->whereHas('lingkungan', function($q) use ($user) {
                    $q->where('id', $user->ketuaLingkungan->lingkungan_id ?? null);
                });
            } elseif ($user->hasRole('umat')) {
                $query->where('user_id', $user->id);
            }
            // No additional filter for super_admin and paroki
            
            // Total bulan saat ini
            $total = $query->count();

            // Status menunggu
            $menunggu = (clone $query)
                ->where('status', 'menunggu')
                ->count();
            
            // Status selesai
            $selesai = (clone $query)
                ->where('status', 'selesai')
                ->count();
            
            $description = "Menunggu: $menunggu | Selesai: $selesai";
            
            $stats[] = Stat::make($label, $total)
                ->description($description)
                ->color($this->getColorForJenisSurat($jenis));
        }

        return $stats;
    }

    private function getColorForJenisSurat(string $jenisSurat): string
    {
        return match ($jenisSurat) {
            'keterangan_kematian' => 'danger',
            'keterangan_lain' => 'primary',
            'pendaftaran_baptis' => 'success',
            'pendaftaran_perkawinan' => 'info',
            default => 'gray',
        };
    }
}