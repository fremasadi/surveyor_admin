<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';

    public ?string $komoditasId = null; // <- Properti untuk menyimpan filter dropdown

    // 1. Tampilkan dropdown di bagian atas chart
    protected function getFormSchema(): array
    {
        return [
            Select::make('komoditasId')
                ->label('Pilih Komoditas')
                ->options(
                    Komoditas::all()->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder('Semua Komoditas'),
        ];
    }

    // 2. Ambil dan kelompokkan data berdasarkan filter komoditas
    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Ambil list komoditas, bisa 1 jika difilter atau semua
        $komoditasList = $this->komoditasId
            ? Komoditas::where('id', $this->komoditasId)->get()
            : Komoditas::all();

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
        return 'line'; // Ganti ke 'bar' jika ingin grafik batang
    }
}
