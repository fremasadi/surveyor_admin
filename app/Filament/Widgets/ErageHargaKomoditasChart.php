<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Harga Rata-rata Komoditas (7 Hari)';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $filters = [];

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

        // Ambil komoditas berdasarkan filter, atau default ke pertama
        $komoditas = null;
        if ($this->filter) {
            $komoditas = Komoditas::find($this->filter);
        }

        if (!$komoditas) {
            $komoditas = Komoditas::orderBy('name')->first();
        }

        if (!$komoditas) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Ambil tanggal 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->translatedFormat('d M');

            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->whereDate('created_at', $date)
                ->where('status', true)
                ->avg('data_input');

            $data[] = $avg !== null ? round($avg, 2) : null;
        }

        return [
            'datasets' => [
                [
                    'label' => $komoditas->name,
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }
}
