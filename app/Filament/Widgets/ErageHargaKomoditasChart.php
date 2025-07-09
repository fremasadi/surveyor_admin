<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas - 7 Hari Terakhir';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Semua Komoditas'];

        $komoditasList = Komoditas::orderBy('name')->get();
        foreach ($komoditasList as $komoditas) {
            $filters[$komoditas->id] = $komoditas->name;
        }

        return $filters;
    }

    protected function getData(): array
    {
        $labels = [];
        $datasets = [];

        // Mendapatkan range tanggal 7 hari terakhir
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6); // 7 hari termasuk hari ini

        // Debug log untuk melihat filter yang dipilih
        Log::info('Filter selected: ' . $this->filter);
        Log::info('Date range: ' . $startDate->toDateString() . ' to ' . $endDate->toDateString());

        // Membuat array tanggal untuk label
        $dateRange = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateRange[] = $date->copy();
            $labels[] = $date->format('d/m'); // Format DD/MM
        }

        // Query builder untuk komoditas dengan filter
        $komoditasQuery = Komoditas::query();

        // Jika ada filter yang dipilih dan bukan 'all', filter berdasarkan ID
        if ($this->filter && $this->filter !== 'all') {
            $komoditasQuery->where('id', $this->filter);
            Log::info('Filtering by komoditas ID: ' . $this->filter);
        } else {
            Log::info('Showing all komoditas');
        }

        $komoditasList = $komoditasQuery->get();

        // Warna untuk setiap komoditas
        $colors = [
            '#3b82f6', // Blue
            '#ef4444', // Red
            '#10b981', // Green
            '#f59e0b', // Yellow
            '#8b5cf6', // Purple
            '#06b6d4', // Cyan
            '#f97316', // Orange
            '#84cc16', // Lime
            '#ec4899', // Pink
            '#6b7280', // Gray
        ];

        $colorIndex = 0;

        foreach ($komoditasList as $komoditas) {
            $komoditasData = [];
            $hasData = false;

            // Ambil data untuk setiap hari dalam range
            foreach ($dateRange as $date) {
                $avg = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $date)
                    ->avg('data_input');

                if ($avg !== null) {
                    $komoditasData[] = round($avg, 2);
                    $hasData = true;
                } else {
                    $komoditasData[] = null; // Null untuk hari tanpa data
                }
            }

            Log::info("Komoditas: {$komoditas->name}, Data: " . json_encode($komoditasData));

            // Hanya tampilkan jika ada data
            if ($hasData) {
                $color = $colors[$colorIndex % count($colors)];
                
                $datasets[] = [
                    'label' => $komoditas->name,
                    'data' => $komoditasData,
                    'borderColor' => $color,
                    'backgroundColor' => $color . '20', // Add transparency
                    'tension' => 0.3,
                    'fill' => false,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'spanGaps' => true, // Menghubungkan garis meskipun ada data kosong
                ];
                
                $colorIndex++;
            }
        }

        // Debug log untuk hasil akhir
        Log::info('Final labels: ' . json_encode($labels));
        Log::info('Final datasets count: ' . count($datasets));

        return [
            'datasets' => $datasets,
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
                    'beginAtZero' => false,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                    ],
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString("id-ID"); }',
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
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