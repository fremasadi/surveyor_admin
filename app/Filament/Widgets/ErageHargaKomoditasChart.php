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

    protected function getFilters(): ?array
    {
        // Filter untuk komoditas
        $komoditasFilters = ['all' => 'Semua Komoditas'];
        
        $komoditasList = Komoditas::orderBy('name')->get();
        foreach ($komoditasList as $komoditas) {
            $komoditasFilters[$komoditas->id] = $komoditas->name;
        }
        
        // Filter untuk rentang tanggal (predefined)
        $dateFilters = [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin', 
            'this_week' => 'Minggu Ini',
            'last_week' => 'Minggu Lalu',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'last_7_days' => '7 Hari Terakhir',
            'last_30_days' => '30 Hari Terakhir',
        ];
        
        // Gabungkan kedua filter dengan separator
        $allFilters = [];
        $allFilters['komoditas'] = '--- PILIH KOMODITAS ---';
        $allFilters = array_merge($allFilters, $komoditasFilters);
        $allFilters['date'] = '--- PILIH RENTANG TANGGAL ---';
        $allFilters = array_merge($allFilters, $dateFilters);
        
        return $allFilters;
    }

    protected function getDateRange(): array
    {
        $filter = $this->filter;
        $today = now();
        
        switch ($filter) {
            case 'today':
                return [$today->format('Y-m-d'), $today->format('Y-m-d')];
            
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                return [$yesterday->format('Y-m-d'), $yesterday->format('Y-m-d')];
            
            case 'this_week':
                return [$today->startOfWeek()->format('Y-m-d'), $today->endOfWeek()->format('Y-m-d')];
            
            case 'last_week':
                $lastWeek = $today->copy()->subWeek();
                return [$lastWeek->startOfWeek()->format('Y-m-d'), $lastWeek->endOfWeek()->format('Y-m-d')];
            
            case 'this_month':
                return [$today->startOfMonth()->format('Y-m-d'), $today->endOfMonth()->format('Y-m-d')];
            
            case 'last_month':
                $lastMonth = $today->copy()->subMonth();
                return [$lastMonth->startOfMonth()->format('Y-m-d'), $lastMonth->endOfMonth()->format('Y-m-d')];
            
            case 'last_7_days':
                return [$today->copy()->subDays(6)->format('Y-m-d'), $today->format('Y-m-d')];
            
            case 'last_30_days':
                return [$today->copy()->subDays(29)->format('Y-m-d'), $today->format('Y-m-d')];
            
            default:
                // Default ke hari ini
                return [$today->format('Y-m-d'), $today->format('Y-m-d')];
        }
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];
        
        // Tentukan apakah ini filter komoditas atau tanggal
        $komoditasFilter = null;
        $dateFilter = 'today'; // default
        
        if ($this->filter) {
            // Cek apakah filter adalah date filter
            $dateFilters = ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'last_7_days', 'last_30_days'];
            
            if (in_array($this->filter, $dateFilters)) {
                $dateFilter = $this->filter;
                $komoditasFilter = 'all';
            } elseif ($this->filter !== 'all' && $this->filter !== 'komoditas' && $this->filter !== 'date') {
                $komoditasFilter = $this->filter;
                $dateFilter = 'today';
            } else {
                $komoditasFilter = 'all';
                $dateFilter = 'today';
            }
        }

        // Dapatkan range tanggal
        [$startDate, $endDate] = $this->getDateRange();

        // Debug log
        Log::info('Komoditas Filter: ' . $komoditasFilter);
        Log::info('Date Filter: ' . $dateFilter);
        Log::info('Date range: ' . $startDate . ' to ' . $endDate);

        // Query builder untuk komoditas
        $komoditasQuery = Komoditas::query();
        
        // Filter komoditas jika diperlukan
        if ($komoditasFilter && $komoditasFilter !== 'all') {
            $komoditasQuery->where('id', $komoditasFilter);
        }
        
        $komoditasList = $komoditasQuery->get();

        foreach ($komoditasList as $komoditas) {
            // Query dengan filter tanggal
            $dataQuery = DataHarian::where('komoditas_id', $komoditas->id)
                ->whereBetween('tanggal', [$startDate, $endDate]);
            
            $avg = $dataQuery->avg('data_input');

            Log::info("Komoditas: {$komoditas->name}, Avg: {$avg}");

            // Hanya tampilkan jika ada data
            if ($avg !== null) {
                $labels[] = $komoditas->name;
                $data[] = round($avg, 2);
            }
        }

        // Debug log untuk hasil akhir
        Log::info('Final labels: ' . json_encode($labels));
        Log::info('Final data: ' . json_encode($data));

        // Label untuk dataset
        $dateRangeLabel = $this->getDateRangeLabel($dateFilter);

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Harga (' . $dateRangeLabel . ')',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDateRangeLabel($filter): string
    {
        switch ($filter) {
            case 'today': return 'Hari Ini';
            case 'yesterday': return 'Kemarin';
            case 'this_week': return 'Minggu Ini';
            case 'last_week': return 'Minggu Lalu';
            case 'this_month': return 'Bulan Ini';
            case 'last_month': return 'Bulan Lalu';
            case 'last_7_days': return '7 Hari Terakhir';
            case 'last_30_days': return '30 Hari Terakhir';
            default: return 'Hari Ini';
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}