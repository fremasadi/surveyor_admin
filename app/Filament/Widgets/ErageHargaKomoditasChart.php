<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $komoditasOptions = Komoditas::pluck('name', 'id')->toArray();

        return [
            null => 'Semua Komoditas',
            ...$komoditasOptions,
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];
    
        $komoditasQuery = Komoditas::query();
    
        if ($this->filter) {
            $komoditasQuery->where('id', $this->filter);
        }
    
        $komoditasList = $komoditasQuery->get();
    
        foreach ($komoditasList as $komoditas) {
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->where('status', 1)
                ->whereNotNull('data_input')
                ->avg(\DB::raw('CAST(data_input AS UNSIGNED)'));
    
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
        return 'line';
    }
}
