<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';
    
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
        $data = [];

        // Debug log untuk melihat filter yang dipilih
        Log::info('Filter selected: ' . $this->filter);

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

        foreach ($komoditasList as $komoditas) {
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->avg('data_input');

            Log::info("Komoditas: {$komoditas->name}, Avg: {$avg}");

            // Hanya tampilkan jika ada data
            if ($avg !== null) {
                $labels[] = $komoditas->name;
                $data[] = round($avg, 2);
            }
        }

        // Debug log untuk hasil akhir
        Log::info('Final labels: ' . json_encode($labels));
        Log::info('Final data: ' . json_encode($data));

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
        return 'line';
    }
}