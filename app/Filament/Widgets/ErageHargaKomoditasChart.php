<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Rata-rata Harga Komoditas (7 Hari Terakhir)';

    protected function getData(): array
    {
        $labels = [];
        $datasets = [];

        // Ambil tanggal 7 hari terakhir
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $dates->push($date);
        }

        // Ambil semua komoditas
        $komoditasList = Komoditas::orderBy('name')->get();

        foreach ($komoditasList as $komoditas) {
            $data = [];

            foreach ($dates as $date) {
                $avg = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $date)
                    ->where('status', true)
                    ->avg('data_input');

                $data[] = $avg !== null ? round($avg, 2) : null;
            }

            $datasets[] = [
                'label' => $komoditas->name,
                'data' => $data,
                'borderColor' => $this->getRandomColor(),
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
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

    // Utility untuk generate warna acak
    protected function getRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    // Buat grafik full width
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }
}
