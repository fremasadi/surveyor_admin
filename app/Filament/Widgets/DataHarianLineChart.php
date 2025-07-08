<?php

namespace App\Filament\Widgets;

use App\Models\DataHarian;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DataHarianLineChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Data Harian (Per Jam)';
    
    protected static ?int $sort = 2;
    
    protected static ?string $maxHeight = '300px';
    
    // Refresh chart setiap 30 detik
    protected static ?string $pollingInterval = '30s';
    
    protected function getData(): array
    {
        $today = Carbon::today();
        
        // Ambil data per jam untuk hari ini
        $data = DataHarian::whereDate('tanggal', $today)
            ->where('status', true)
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');
        
        // Siapkan data untuk 24 jam (0-23)
        $hours = [];
        $values = [];
        
        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
            $values[] = $data->get($i)->total ?? 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Data per Jam',
                    'data' => $values,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $hours,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}