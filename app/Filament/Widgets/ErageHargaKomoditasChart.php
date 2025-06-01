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

        // Jika ada filter yang dipilih, ambil data untuk komoditas tertentu saja
        if ($this->filter) {
            $komoditas = Komoditas::find($this->filter);
            if ($komoditas) {
                $avg = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereNotNull('data_input')
                    ->where('data_input', '>', 0)
                    ->avg('data_input');
                    
                $count = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereNotNull('data_input')
                    ->where('data_input', '>', 0)
                    ->count();

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
                ->with('komoditas')
                ->get();

            foreach ($avgData as $item) {
                if ($item->komoditas && $item->avg_price !== null) {
                    $labels[] = $item->komoditas->name;
                    $data[] = round($item->avg_price, 2);
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