<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\Komoditas;
use App\Models\DataHarian;
use App\Models\Responden;
use Carbon\Carbon; // Tambahkan Carbon

class DashboardStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Jumlah Surveyor', User::where('role', 'surveyor')->count())
                ->description('Total surveyor')
                ->color('success'),

            Card::make('Jumlah Komoditas', Komoditas::count())
                ->description('Total komoditas yang terdaftar')
                ->color('info'),

            Card::make('Jumlah Penjual', Responden::count())
                ->description('Total Penjual yang terdaftar')
                ->color('danger'),

            Card::make('Jumlah Data Harian Hari Ini', DataHarian::whereDate('created_at', Carbon::today())->count())
                ->description('data harian  hari ini')
                ->color('success'),
        ];
    }
}
