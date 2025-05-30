<?php

namespace App\Filament\Widgets;

use App\Models\Surat;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Surat (Selesai)';

    protected static ?int $sort = 1;
    
    protected static string $color = 'info';
    
    public ?string $filter = null;

    protected static ?string $pollingInterval = '5s';

    protected static ?string $cacheKey = 'surat-chart-data';

    protected function getFilters(): ?array
    {
        $query = Surat::query();
        
        // Apply role-based filtering
        $user = User::where('id', Auth::id())->first();
        
        if ($user->hasRole('ketua_lingkungan')) {
            $query->whereHas('lingkungan', function($q) use ($user) {
                $q->where('id', $user->ketuaLingkungan->lingkungan_id ?? null);
            });
        } elseif ($user->hasRole('umat')) {
            $query->where('user_id', $user->id);
        }
        // No additional filter for super_admin and paroki
        
        $years = $query->select(DB::raw('YEAR(tgl_surat) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->toArray();

        return empty($years) ? null : array_combine($years, $years);
    }

    protected function getData(): array
    {
        $selectedYear = $this->filter ?: now()->year;

        $jenisSurat = [
            'keterangan_kematian' => 'Keterangan Kematian',
            'keterangan_lain' => 'Keterangan Lain',
            'pendaftaran_baptis' => 'Pendaftaran Baptis',
            'pendaftaran_perkawinan' => 'Pendaftaran Perkawinan'
        ];

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // Gunakan cache dengan tag untuk invalidasinya
        $cacheKey = self::$cacheKey . '-' . $selectedYear . '-' . Auth::id();
        
        return cache()->remember($cacheKey, now()->addSeconds(5), function() use ($selectedYear, $jenisSurat, $months) {
            $datasets = [];
            
            $user = User::where('id', Auth::id())->first();
            
            foreach ($jenisSurat as $key => $label) {
                $data = [];
                
                // Base query
                $query = Surat::where('jenis_surat', $key)
                    ->where('status', 'selesai')
                    ->whereYear('tgl_surat', $selectedYear);
                
                // Apply role-based filtering
                if ($user->hasRole('ketua_lingkungan')) {
                    $query->whereHas('lingkungan', function($q) use ($user) {
                        $q->where('id', $user->ketuaLingkungan->lingkungan_id ?? null);
                    });
                } elseif ($user->hasRole('umat')) {
                    $query->where('user_id', $user->id);
                }
                // No additional filter for super_admin and paroki
                
                $counts = $query->selectRaw('MONTH(tgl_surat) as month, COUNT(*) as count')
                    ->groupBy('month')
                    ->pluck('count', 'month')
                    ->toArray();
                
                foreach ($months as $monthNum => $monthName) {
                    $data[] = $counts[$monthNum] ?? 0;
                }
                
                $color = $this->getColorForJenisSurat($key);
                
                $datasets[] = [
                    'label' => $label,
                    'data' => $data,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'borderWidth' => 1,
                ];
            }

            return [
                'datasets' => $datasets,
                'labels' => array_values($months),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'animation' => [
                'duration' => 500, // Animasi lebih cepat saat update
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'stacked' => false,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 20,
                        'usePointStyle' => true,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }

    private function getColorForJenisSurat(string $jenisSurat): string
    {
        return match ($jenisSurat) {
            'keterangan_kematian' => 'rgba(255, 99, 132, 0.7)',
            'keterangan_lain' => 'rgba(255, 206, 86, 0.7)',
            'pendaftaran_baptis' => 'rgba(75, 192, 192, 0.7)',
            'pendaftaran_perkawinan' => 'rgba(54, 162, 235, 0.7)',
            default => 'rgba(153, 102, 255, 0.7)',
        };
    }

    protected function getColumns(): int
    {
        return 'full';
    }

    protected function getMaxHeight(): ?string
    {
        return '400px';
    }
}