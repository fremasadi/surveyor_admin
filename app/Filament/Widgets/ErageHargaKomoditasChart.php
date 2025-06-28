<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas - Hari Ini';
    
    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Semua Komoditas'];
        
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
        
        // Mendapatkan tanggal hari ini
        $today = Carbon::today();

        // Debug log untuk melihat filter yang dipilih
        Log::info('Filter selected: ' . $this->filter);
        Log::info('Date filter: ' . $today->toDateString());

        // Query builder untuk komoditas dengan filter
        $komoditasQuery = Komoditas::query();
        
        // Jika ada filter yang dipilih dan bukan 'all', filter berdasarkan ID
        if ($this->filter && $this->filter !== 'all') {
            $komoditasQuery->where('id', $this->filter);
            Log::info('Filtering by komoditas ID: ' . $this->filter);
        } else {
            Log::info('Showing all komoditas');
        }
        
        $komoditasList = $komoditasQuery->get();
        
        // Debug log untuk melihat komoditas yang diambil
        foreach ($komoditasList as $komoditas) {
            Log::info('Processing komoditas: ' . $komoditas->name . ' (ID: ' . $komoditas->id . ')');
        }

        foreach ($komoditasList as $komoditas) {
            // Filter data harian berdasarkan komoditas_id dan created_at hari ini
            $avg = DataHarian::where('komoditas_id', $komoditas->id)
                ->whereDate('created_at', $today)
                ->avg('data_input');

            Log::info("Komoditas: {$komoditas->name}, Avg Today: {$avg}");

            // Hanya tampilkan jika ada data
            if ($avg !== null) {
                $labels[] = $komoditas->name;
                $data[] = round($avg, 2);
            }
        }

        // Debug log untuk hasil akhir
        Log::info('Final labels: ' . json_encode($labels));
        Log::info('Final data: ' . json_encode($data));

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Harga Hari Ini',
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