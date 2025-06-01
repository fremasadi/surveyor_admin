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
        $komoditasOptions = [];
        
        // Tambahkan ID + 1 untuk menghindari konflik dengan index 0
        $komoditas = Komoditas::all();
        foreach ($komoditas as $item) {
            $komoditasOptions[$item->id + 1] = $item->name;
        }
        
        return [
            null => 'Semua Komoditas',
            ...$komoditasOptions
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Debug log
        \Log::info('Filter value: ' . ($this->filter ?? 'null'));

        // Jika ada filter yang dipilih, ambil data untuk komoditas tertentu saja
        if ($this->filter) {
            // Kurangi 1 dari filter untuk mendapatkan ID asli
            $realKomoditasId = $this->filter - 1;
            
            \Log::info('Real komoditas ID: ' . $realKomoditasId);
            
            $komoditas = Komoditas::find($realKomoditasId);
            if ($komoditas) {
                $avg = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereNotNull('data_input')
                    ->where('data_input', '>', 0)
                    ->avg('data_input');
                    
                $count = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereNotNull('data_input')
                    ->where('data_input', '>', 0)
                    ->count();

                \Log::info("Komoditas: {$komoditas->name}, Count: {$count}, Avg: {$avg}");

                if ($avg !== null && $count > 0) {
                    $labels[] = $komoditas->name;
                    $data[] = round($avg, 2);
                }
            }
        } else {
            // Untuk semua komoditas, kelompokkan berdasarkan komoditas_id
            $avgData = DataHarian::select('komoditas_id')
                ->selectRaw('AVG(data_input) as avg_price')
                ->selectRaw('COUNT(*) as total_data')
                ->whereNotNull('data_input')
                ->where('data_input', '>', 0)
                ->groupBy('komoditas_id')
                ->get();

            \Log::info('Showing all komoditas, found: ' . $avgData->count() . ' items');

            foreach ($avgData as $item) {
                $komoditas = Komoditas::find($item->komoditas_id);
                if ($komoditas && $item->avg_price !== null) {
                    $labels[] = $komoditas->name;
                    $data[] = round($item->avg_price, 2);
                    \Log::info("All mode - Komoditas: {$komoditas->name}, Avg: {$item->avg_price}");
                }
            }
        }

        // Jika tidak ada data sama sekali
        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Rata-rata Harga',
                        'data' => [0],
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'tension' => 0.3,
                    ],
                ],
                'labels' => ['Tidak ada data'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Harga',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // bisa juga coba 'bar'
    }
}