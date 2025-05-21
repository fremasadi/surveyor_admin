<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        $komoditasList = Komoditas::all();

        foreach ($komoditasList as $komoditas) {
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->avg('data_input');

            $labels[] = $komoditas->name;
            $data[] = round($avg, 2);
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
