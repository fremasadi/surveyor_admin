<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class DailyDataChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Harga Komoditas Harian (Status True)';
    
    protected static string $color = 'success';
    
    protected static ?int $sort = 2;
    
    // Hapus maxHeight agar chart bisa full
    // protected static ?string $maxHeight = '300px';
    
    public ?string $filter = 'today';
    
    protected function getData(): array
    {
        $activeFilter = $this->filter ?? 'today';
        
        // Tentukan rentang tanggal berdasarkan filter
        $query = DataHarian::where('status', true);
        
        switch ($activeFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
        }
        
        // Mengambil data harian dengan status true
        $todayData = $query->with(['komoditas'])
            ->get()
            ->groupBy('komoditas.name');
        
        // Membuat array untuk menyimpan data chart
        $labels = [];
        $datasets = [];
        
        // Jika ada data
        if ($todayData->isNotEmpty()) {
            // Ambil nama komoditas sebagai label
            $labels = $todayData->keys()->toArray();
            
            // Warna untuk chart
            $colors = [
                'rgba(255, 99, 132, 0.8)',   // Red
                'rgba(54, 162, 235, 0.8)',   // Blue
                'rgba(255, 205, 86, 0.8)',   // Yellow
                'rgba(75, 192, 192, 0.8)',   // Green
                'rgba(153, 102, 255, 0.8)',  // Purple
                'rgba(255, 159, 64, 0.8)',   // Orange
                'rgba(199, 199, 199, 0.8)',  // Grey
                'rgba(83, 102, 147, 0.8)',   // Dark Blue
                'rgba(255, 99, 255, 0.8)',   // Pink
                'rgba(99, 255, 132, 0.8)',   // Light Green
            ];
            
            $borderColors = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(199, 199, 199, 1)',
                'rgba(83, 102, 147, 1)',
                'rgba(255, 99, 255, 1)',
                'rgba(99, 255, 132, 1)',
            ];
            
            // Hitung rata-rata harga untuk setiap komoditas
            $averagePrices = [];
            $backgroundColors = [];
            $borderColorsData = [];
            
            $colorIndex = 0;
            foreach ($todayData as $komoditasName => $data) {
                $totalPrice = 0;
                $count = 0;
                
                foreach ($data as $item) {
                    $harga = $this->extractPrice($item->data_input);
                    if ($harga !== null) {
                        $totalPrice += $harga;
                        $count++;
                    }
                }
                
                $averagePrice = $count > 0 ? $totalPrice / $count : 0;
                $averagePrices[] = $averagePrice;
                $backgroundColors[] = $colors[$colorIndex % count($colors)];
                $borderColorsData[] = $borderColors[$colorIndex % count($borderColors)];
                $colorIndex++;
            }
            
            // Dataset untuk bar chart
            $datasets[] = [
                'label' => 'Harga Rata-rata (Rp)',
                'data' => $averagePrices,
                'backgroundColor' => $backgroundColors,
                'borderColor' => $borderColorsData,
                'borderWidth' => 2,
                'barThickness' => 50,
                'maxBarThickness' => 60,
            ];
        } else {
            // Jika tidak ada data, buat chart kosong
            $labels = ['Tidak ada data'];
            $datasets = [
                [
                    'label' => 'Tidak ada data',
                    'data' => [0],
                    'backgroundColor' => 'rgba(199, 199, 199, 0.5)',
                    'borderColor' => 'rgba(199, 199, 199, 1)',
                    'borderWidth' => 2,
                ]
            ];
        }
        
        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar'; // Menggunakan bar chart untuk menampilkan per komoditas
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Grafik Harga Komoditas (Status: True)',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold'
                    ]
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            return context.dataset.label + ": Rp " + 
                                   new Intl.NumberFormat("id-ID").format(context.parsed.y); 
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nama Komoditas',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold'
                        ]
                    ],
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                        'font' => [
                            'size' => 12
                        ]
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold'
                        ]
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { 
                            return "Rp " + new Intl.NumberFormat("id-ID").format(value); 
                        }',
                        'font' => [
                            'size' => 12
                        ]
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
            ],
            'layout' => [
                'padding' => [
                    'left' => 20,
                    'right' => 20,
                    'top' => 20,
                    'bottom' => 20
                ]
            ]
        ];
    }
    
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
        ];
    }
    
    /**
     * Extract price from data_input JSON field
     */
    private function extractPrice($dataInput): ?float
    {
        if (empty($dataInput)) {
            return null;
        }
        
        // Jika sudah berupa array
        if (is_array($dataInput)) {
            $data = $dataInput;
        } else {
            // Decode JSON
            $data = json_decode($dataInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
        }
        
        // Coba berbagai kemungkinan nama field untuk harga
        $priceFields = [
            'harga', 'price', 'harga_jual', 'harga_beli', 
            'harga_pasar', 'harga_retail', 'harga_grosir',
            'cost', 'value', 'amount'
        ];
        
        foreach ($priceFields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                return (float) $data[$field];
            }
        }
        
        // Jika data_input langsung berupa angka
        if (is_numeric($data)) {
            return (float) $data;
        }
        
        return null;
    }
}