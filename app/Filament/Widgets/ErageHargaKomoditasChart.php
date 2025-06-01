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
        $komoditasOptions = Komoditas::pluck('name', 'id')->toArray();
        
        return [
            null => 'Semua Komoditas',
            ...$komoditasOptions
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Query builder untuk komoditas dengan filter
        $komoditasQuery = Komoditas::query();
        
        // Jika ada filter yang dipilih, filter berdasarkan ID
        if ($this->filter) {
            $komoditasQuery->where('id', $this->filter);
        }
        
        $komoditasList = $komoditasQuery->get();

        foreach ($komoditasList as $komoditas) {
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->avg('data_input');

            // Hanya tampilkan jika ada data
            if ($avg !== null) {
                $labels[] = $komoditas->name;
                $data[] = round($avg, 2);
            }
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