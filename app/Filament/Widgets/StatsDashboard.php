<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsDashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Keterangan Kematian', '192.1k'),
            Stat::make('Keterangan Lain', '21%'),
            Stat::make('Pendaftaran Baptis', '3:12'),
            Stat::make('Pendaftaran Kanonik Perkawinan', '3:12'),
        ];
    }
}
