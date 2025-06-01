<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';
    
    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $komoditasOptions = Komoditas::orderBy('name')->pluck('name', 'id')->toArray();
        
        return [
            '' => 'Semua Komoditas',
            ...$komoditasOptions
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Debug filter value
        \Log::info('Current filter value: ' . ($this->filter ?? 'null'));

        // Jika filter kosong atau null, tampilkan semua
        if (empty($this->filter) || $this->filter === '' || $this->filter === null) {
            // Ambil data untuk semua komoditas dengan grouping
            $results = DataHarian::select('komoditas_id')
                ->selectRaw('AVG(CAST(data_input AS DECIMAL(10,2))) as avg_price')
                ->selectRaw('COUNT(*) as total_records')
                ->whereNotNull('data_input')
                ->where('data_input', '!=', '')
                ->where('data_input', '>', 0)
                ->groupBy('komoditas_id')
                ->get();

            foreach ($results as $result) {
                $komoditas = Komoditas::find($result->komoditas_id);
                if ($komoditas && $result->avg_price > 0) {
                    $labels[] = $komoditas->name;
                    $data[] = round($result->avg_price, 2);
                }
            }
        } else {
            // Filter untuk komoditas tertentu
            $komoditas = Komoditas::find($this->filter);
            
            if ($komoditas) {
                $avg = DataHarian::where('komoditas_id', $this->filter)
                    ->whereNotNull('data_input')
                    ->where('data_input', '!=', '')
                    ->where('data_input', '>', 0)
                    ->avg('data_input');
                
                $count = DataHarian::where('komoditas_id', $this->filter)
                    ->whereNotNull('data_input')
                    ->where('data_input', '!=', '')
                    ->where('data_input', '>', 0)
                    ->count();

                \Log::info("Komoditas: {$komoditas->name}, Count: {$count}, Avg: {$avg}");

                if ($avg !== null && $avg > 0) {
                    $labels[] = $komoditas->name;
                    $data[] = round($avg, 2);
                }
            }
        }

        // Jika tidak ada data
        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Rata-rata Harga',
                        'data' => [0],
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    ],
                ],
                'labels' => ['Tidak ada data'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Harga (Rp)',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
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
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString(); }'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString(); }'
                    ]
                ]
            ]
        ];
    }
}