<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Illuminate\Support\Facades\DB;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';

    protected function getFilters(): array
    {
        return [
            'komoditas' => [
                'label' => 'Komoditas',
                'options' => Komoditas::pluck('name', 'id')->toArray(),
            ],
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        $filterKomoditas = $this->filters['komoditas'] ?? null;

        $komoditasQuery = Komoditas::query();

        if ($filterKomoditas) {
            $komoditasQuery->where('id', $filterKomoditas);
        }

        $komoditasList = $komoditasQuery->get();

        foreach ($komoditasList as $komoditas) {
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->where('status', 1)
                ->whereNotNull('data_input')
                ->avg(DB::raw('CAST(data_input AS UNSIGNED)'));

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
