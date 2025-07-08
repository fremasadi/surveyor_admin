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
    protected static ?string $heading = 'Rata-rata Harga per Komoditas - Hari Ini';
    
    public ?string $filter = null;

    // protected function getFilters(): ?array
    // {
    //     $filters = ['all' => 'Semua Komoditas'];
        
    //     $komoditasList = Komoditas::orderBy('name')->get();
    //     foreach ($komoditasList as $komoditas) {
    //         $filters[$komoditas->id] = $komoditas->name;
    //     }
        
    //     return $filters;
    // }

    protected function getData(): array
    {
        $labels = [];
        $datasets = [];
        
        // Mendapatkan tanggal hari ini
        $today = Carbon::today();

        // Debug log untuk melihat filter yang dipilih
        Log::info('Filter selected: ' . $this->filter);
        Log::info('Date filter: ' . $today->toDateString());

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
        
        // Debug log untuk melihat komoditas yang diambil
        foreach ($komoditasList as $komoditas) {
            Log::info('Processing komoditas: ' . $komoditas->name . ' (ID: ' . $komoditas->id . ')');
        }

        // Warna untuk setiap dataset
        $colors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#6b7280',
            '#14b8a6', '#f43f5e', '#3b82f6', '#ef4444', '#10b981'
        ];

        // Jika menampilkan semua komoditas, buat dataset terpisah untuk setiap komoditas
        if ($this->filter === 'all' || $this->filter === null) {
            $colorIndex = 0;
            
            foreach ($komoditasList as $komoditas) {
                // Ambil data harian untuk komoditas ini
                $dataHarian = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->orderBy('created_at')
                    ->get();

                if ($dataHarian->isNotEmpty()) {
                    $komoditasData = [];
                    $komoditasLabels = [];
                    
                    foreach ($dataHarian as $data) {
                        $komoditasData[] = $data->data_input;
                        $komoditasLabels[] = $data->created_at->format('H:i');
                    }

                    // Jika labels kosong, gunakan labels dari komoditas pertama
                    if (empty($labels)) {
                        $labels = $komoditasLabels;
                    }

                    $datasets[] = [
                        'label' => $komoditas->name,
                        'data' => $komoditasData,
                        'borderColor' => $colors[$colorIndex % count($colors)],
                        'backgroundColor' => $colors[$colorIndex % count($colors)] . '20',
                        'tension' => 0.3,
                        'fill' => false,
                        'pointBackgroundColor' => $colors[$colorIndex % count($colors)],
                        'pointBorderColor' => $colors[$colorIndex % count($colors)],
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                    ];

                    $colorIndex++;
                }
            }
            
            // Jika tidak ada data untuk hari ini, tampilkan rata-rata seperti sebelumnya
            if (empty($datasets)) {
                $data = [];
                $labels = [];
                
                foreach ($komoditasList as $komoditas) {
                    $avg = DataHarian::where('komoditas_id', $komoditas->id)
                        ->whereDate('created_at', $today)
                        ->avg('data_input');

                    if ($avg !== null) {
                        $labels[] = $komoditas->name;
                        $data[] = round($avg, 2);
                    }
                }
                
                $datasets[] = [
                    'label' => 'Rata-rata Harga Hari Ini',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#3b82f6',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ];
            }
        } else {
            // Jika filter komoditas spesifik, tampilkan data per waktu
            $komoditas = Komoditas::find($this->filter);
            if ($komoditas) {
                $dataHarian = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->orderBy('created_at')
                    ->get();

                $data = [];
                foreach ($dataHarian as $dataItem) {
                    $labels[] = $dataItem->created_at->format('H:i');
                    $data[] = $dataItem->data_input;
                }

                $datasets[] = [
                    'label' => $komoditas->name,
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                    'fill' => false,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#3b82f6',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ];
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
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu / Komoditas',
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
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }
}