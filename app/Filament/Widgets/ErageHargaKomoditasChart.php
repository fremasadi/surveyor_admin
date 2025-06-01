<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';

    // Menyimpan filter terpilih
    public ?array $filters = [];

    protected function getFilters(): array
    {
        return [
            'komoditas' => [
                'label' => 'Komoditas',
                'options' => [null => 'Semua Komoditas'] + Komoditas::pluck('name', 'id')->toArray(),
            ],
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Ambil filter komoditas jika ada
        $selectedKomoditasId = $this->filters['komoditas'] ?? null;

        // Filter data berdasarkan komoditas terpilih
        $komoditasQuery = Komoditas::query();
        if ($selectedKomoditasId) {
            $komoditasQuery->where('id', $selectedKomoditasId);
        }

        $komoditasList = $komoditasQuery->get();

        foreach ($komoditasList as $komoditas) {
            $average = DataHarian::where('komoditas_id', $komoditas->id)->avg('data_input');

            if ($average !== null) {
                $labels[] = $komoditas->name;
                $data[] = round($average, 2);
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
        return 'line'; // atau 'bar'
    }
}
