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
    protected static ?string $heading = 'Rata-rata Harga per Komoditas - Hari Ini (Status Aktif)';
    
    public ?string $filter = null;
    
    // Mengatur ukuran chart menjadi full column
    protected int | string | array $columnSpan = 'full';
    
    // Mengatur tinggi chart
    protected static ?string $maxHeight = '400px';

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
        Log::info('Only showing data with status = true');

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
                // Ambil data harian untuk komoditas ini dengan status true
                $dataHarian = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->where('status', true)
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

                    Log::info("Komoditas: {$komoditas->name}, Data points (status=true): " . count($komoditasData));
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
                        ->where('status', true)
                        ->avg('data_input');

                    if ($avg !== null) {
                        $labels[] = $komoditas->name;
                        $data[] = round($avg, 2);
                        Log::info("Komoditas: {$komoditas->name}, Avg Today (status=true): {$avg}");
                    }
                }
                
                $datasets[] = [
                    'label' => 'Rata-rata Harga Hari Ini (Status Aktif)',
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
                    ->where('status', true)
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
            'aspectRatio' => 3, // Mengatur rasio aspek untuk chart yang lebih lebar
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold'
                        ]
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11
                        ]
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu / Komoditas',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold'
                        ]
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11
                        ]
                    ]
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 12
                        ],
                        'padding' => 20,
                        'usePointStyle' => true,
                    ]
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleFont' => [
                        'size' => 13,
                        'weight' => 'bold'
                    ],
                    'bodyFont' => [
                        'size' => 12
                    ],
                    'padding' => 12,
                    'cornerRadius' => 8,
                ],
                'title' => [
                    'display' => false, // Menggunakan heading widget instead
                ]
            ],
            'elements' => [
                'point' => [
                    'radius' => 5,
                    'hoverRadius' => 8,
                    'borderWidth' => 2,
                ],
                'line' => [
                    'borderWidth' => 3,
                ]
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'layout' => [
                'padding' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ]
            ]
        ];
    }
}