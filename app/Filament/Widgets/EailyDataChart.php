<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EailyDataChart extends ChartWidget
{
    protected static ?string $heading = 'Harga Harian Komoditas';

    protected function getData(): array
    {
        $today = Carbon::today();

        // Ambil data harian yang status = true dan tanggal = hari ini
        $data = DataHarian::whereDate('tanggal', $today)
            ->where('status', true)
            ->with('komoditas') // agar kita bisa akses nama komoditas
            ->get();

        // Kelompokkan dan rata-rata (jika perlu)
        $grouped = $data->groupBy('komoditas.name')->map(function ($items) {
            return $items->avg('data_input'); // bisa juga pakai max, sum, dll.
        });

        return [
            'datasets' => [
                [
                    'label' => 'Harga (Rp)',
                    'data' => $grouped->values()->toArray(),
                    'backgroundColor' => '#3b82f6', // biru
                ],
            ],
            'labels' => $grouped->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // atau 'line', 'pie', dll
    }
}
