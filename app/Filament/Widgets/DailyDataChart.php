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
    
    protected static ?string $maxHeight = '300px';
    
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
        $chartData = [];
        $labels = [];
        $datasets = [];
        
        // Jika ada data
        if ($todayData->isNotEmpty()) {
            // Mengambil semua jam dari 00:00 sampai 23:00
            $hours = collect(range(0, 23))->map(function ($hour) {
                return sprintf('%02d:00', $hour);
            });
            
            $labels = $hours->toArray();
            
            // Warna untuk setiap komoditas
            $colors = [
                'rgb(255, 99, 132)',   // Red
                'rgb(54, 162, 235)',   // Blue
                'rgb(255, 205, 86)',   // Yellow
                'rgb(75, 192, 192)',   // Green
                'rgb(153, 102, 255)',  // Purple
                'rgb(255, 159, 64)',   // Orange
                'rgb(199, 199, 199)',  // Grey
                'rgb(83, 102, 147)',   // Dark Blue
                'rgb(255, 99, 255)',   // Pink
                'rgb(99, 255, 132)',   // Light Green
            ];
            
            $colorIndex = 0;
            
            // Proses setiap komoditas
            foreach ($todayData as $komoditasName => $data) {
                // Inisialisasi data untuk setiap jam
                $hourlyData = array_fill(0, 24, null);
                
                // Hitung rata-rata harga untuk setiap jam
                foreach ($data as $item) {
                    $hour = Carbon::parse($item->created_at)->hour;
                    
                    // Ambil harga dari data_input menggunakan helper method
                    $harga = $this->extractPrice($item->data_input);
                    
                    // Jika ada harga, simpan atau hitung rata-rata jika sudah ada data di jam tersebut
                    if ($harga !== null) {
                        $hourlyData[$hour] = $this->calculateAveragePrice($hourlyData[$hour], $harga);
                    }
                }
                
                // Konversi null ke 0 untuk chart (atau bisa diubah sesuai kebutuhan)
                $hourlyData = array_map(function($value) {
                    return $value === null ? 0 : $value;
                }, $hourlyData);
                
                // Tambahkan dataset untuk komoditas ini
                $datasets[] = [
                    'label' => $komoditasName ?? 'Tidak Diketahui',
                    'data' => $hourlyData,
                    'borderColor' => $colors[$colorIndex % count($colors)],
                    'backgroundColor' => $colors[$colorIndex % count($colors)] . '20', // Transparansi 20%
                    'fill' => false,
                    'tension' => 0.1,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                ];
                
                $colorIndex++;
            }
        } else {
            // Jika tidak ada data, buat chart kosong
            $labels = ['00:00', '06:00', '12:00', '18:00', '23:00'];
            $datasets = [
                [
                    'label' => 'Tidak ada data',
                    'data' => [0, 0, 0, 0, 0],
                    'borderColor' => 'rgb(199, 199, 199)',
                    'backgroundColor' => 'rgba(199, 199, 199, 0.1)',
                    'fill' => false,
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
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Grafik Harga Komoditas per Jam (Status: True)',
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
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
                        'text' => 'Jam (24 Hour Format)',
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { 
                            return "Rp " + new Intl.NumberFormat("id-ID").format(value); 
                        }',
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderWidth' => 2,
                ],
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 5,
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
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
    
    /**
     * Calculate average price for hour with existing data
     */
    private function calculateAveragePrice(?float $existingPrice, float $newPrice): float
    {
        if ($existingPrice === null) {
            return $newPrice;
        }
        
        return ($existingPrice + $newPrice) / 2;
    }
}