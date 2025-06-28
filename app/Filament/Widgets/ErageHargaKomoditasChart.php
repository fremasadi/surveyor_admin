<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas';
    
    public ?string $filter = null;
    public ?string $filterStartDate = null;
    public ?string $filterEndDate = null;

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Semua Komoditas'];
        
        $komoditasList = Komoditas::orderBy('name')->get();
        foreach ($komoditasList as $komoditas) {
            $filters[$komoditas->id] = $komoditas->name;
        }
        
        return $filters;
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('filter')
                ->label('Komoditas')
                ->options($this->getFilters())
                ->default('all'),
            
            DatePicker::make('filterStartDate')
                ->label('Tanggal Mulai')
                ->default(now()->format('Y-m-d'))
                ->native(false),
            
            DatePicker::make('filterEndDate')
                ->label('Tanggal Akhir')
                ->default(now()->format('Y-m-d'))
                ->native(false),
        ];
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        // Set default tanggal jika belum dipilih
        $startDate = $this->filterStartDate ?: now()->format('Y-m-d');
        $endDate = $this->filterEndDate ?: now()->format('Y-m-d');

        // Debug log untuk melihat filter yang dipilih
        Log::info('Filter selected: ' . $this->filter);
        Log::info('Date range: ' . $startDate . ' to ' . $endDate);

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
            // Query dengan filter tanggal
            $dataQuery = DataHarian::where('komoditas_id', $komoditas->id);
            
            // Tambahkan filter tanggal
            if ($startDate && $endDate) {
                $dataQuery->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $dataQuery->where('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $dataQuery->where('tanggal', '<=', $endDate);
            }
            
            $avg = $dataQuery->avg('data_input');

            Log::info("Komoditas: {$komoditas->name}, Avg: {$avg}, Date range: {$startDate} to {$endDate}");

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
                    'label' => 'Rata-rata Harga (' . $startDate . ' - ' . $endDate . ')',
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