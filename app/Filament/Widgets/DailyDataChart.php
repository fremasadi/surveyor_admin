<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class DailyDataChart extends ChartWidget
{
    protected static ?string $heading = 'Data Harga Komoditas Harian';
    
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Ambil data harian untuk hari ini dengan status true (1)
        $today = Carbon::today();
        
        $data = DataHarian::with('komoditas')
            ->where('tanggal', $today)
            ->where('status', 1) // status true
            ->get()
            ->groupBy('komoditas_id')
            ->map(function ($items) {
                // Ambil rata-rata harga jika ada multiple data untuk komoditas yang sama
                return [
                    'komoditas_name' => $items->first()->komoditas->name,
                    'average_price' => $items->avg('data_input')
                ];
            })
            ->values();

        // Siapkan data untuk chart
        $labels = $data->pluck('komoditas_name')->toArray();
        $prices = $data->pluck('average_price')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Harga (Rp)',
                    'data' => $prices,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString("id-ID");
                        }'
                    ]
                ]
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nama Komoditas'
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)'
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + value.toLocaleString("id-ID");
                        }'
                    ]
                ],
            ],
        ];
    }

    // Method untuk refresh data secara otomatis
    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh setiap 30 detik
    }

    // Method untuk filter data berdasarkan tanggal tertentu (optional)
    public function getDataForDate(Carbon $date): array
    {
        $data = DataHarian::with('komoditas')
            ->where('tanggal', $date)
            ->where('status', 1)
            ->get()
            ->groupBy('komoditas_id')
            ->map(function ($items) {
                return [
                    'komoditas_name' => $items->first()->komoditas->name,
                    'average_price' => $items->avg('data_input')
                ];
            })
            ->values();

        return [
            'labels' => $data->pluck('komoditas_name')->toArray(),
            'prices' => $data->pluck('average_price')->toArray(),
        ];
    }
}