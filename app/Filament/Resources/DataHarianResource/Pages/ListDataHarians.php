<?php

namespace App\Filament\Resources\DataHarianResource\Pages;

use App\Filament\Resources\DataHarianResource;
use App\Models\DataHarian;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Resources\Pages\ListRecords;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ListDataHarians extends ListRecords
{
    protected static string $resource = DataHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('exportPDF')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Select::make('filter_type')
                        ->label('Jenis Filter')
                        ->options([
                            'daily' => 'Harian',
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulan Tertentu',
                            'custom' => 'Kustom',
                        ])
                        ->default('daily')
                        ->reactive()
                        ->required(),
                    
                    // Grid untuk opsi harian
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('created_at')
                                ->label('created_at')
                                ->visible(fn (callable $get) => $get('filter_type') === 'daily')
                                ->default(now())
                                ->required()
                        ]),
                    
                    // Grid untuk opsi mingguan
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('week_date')
                                ->label('Pilih minggu dari created_at')
                                ->visible(fn (callable $get) => $get('filter_type') === 'weekly')
                                ->default(now())
                                ->required()
                        ]),
                    
                    // Grid untuk opsi bulanan
                    Grid::make(2)
                        ->schema([
                            Select::make('month')
                                ->label('Bulan')
                                ->visible(fn (callable $get) => $get('filter_type') === 'monthly')
                                ->options([
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember',
                                ])
                                ->default(now()->format('m'))
                                ->required(),
                            
                            Select::make('year')
                                ->label('Tahun')
                                ->visible(fn (callable $get) => $get('filter_type') === 'monthly')
                                ->options(function() {
                                    $years = [];
                                    $currentYear = (int)now()->format('Y');
                                    for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                                        $years[$i] = (string)$i;
                                    }
                                    return $years;
                                })
                                ->default(now()->format('Y'))
                                ->required()
                        ]),
                    
                    // Grid untuk opsi kustom
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('created_at Mulai')
                                ->visible(fn (callable $get) => $get('filter_type') === 'custom')
                                ->default(now()->subDays(7))
                                ->required(),
                            
                            DatePicker::make('end_date')
                                ->label('created_at Akhir')
                                ->visible(fn (callable $get) => $get('filter_type') === 'custom')
                                ->default(now())
                                ->required()
                        ]),
                ])
                ->action(function (array $data) {
                    // Menerapkan filter berdasarkan jenis yang dipilih
                    $query = DataHarian::query()
                        ->with(['user', 'komoditas', 'responden']);

                    switch ($data['filter_type']) {
                        case 'daily':
                            $date = Carbon::parse($data['created_at'])->toDateString();
                            $query->whereDate('created_at', $date);
                            $period = "Harian: " . Carbon::parse($date)->format('d-m-Y');
                            break;
                            
                        case 'weekly':
                            $refDate = Carbon::parse($data['week_date']);
                            $startOfWeek = $refDate->copy()->startOfWeek();
                            $endOfWeek = $refDate->copy()->endOfWeek();
                            $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                            $period = "Mingguan: " . $startOfWeek->format('d-m-Y') . " s/d " . $endOfWeek->format('d-m-Y');
                            break;
                            
                        case 'monthly':
                            $month = $data['month'];
                            $year = $data['year'];
                            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                            
                            // Format nama bulan dalam Bahasa Indonesia
                            $monthNames = [
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ];
                            $period = "Bulanan: " . $monthNames[$month] . " " . $year;
                            break;
                            
                        case 'custom':
                            $startDate = Carbon::parse($data['start_date'])->startOfDay();
                            $endDate = Carbon::parse($data['end_date'])->endOfDay();
                            $query->whereBetween('created_at', [$startDate, $endDate]);
                            $period = "Kustom: " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y');
                            break;
                    }

                    // Debugging
                    \Log::info('Export PDF Data Query', [
                        'filter_type' => $data['filter_type'],
                        'period' => $period,
                        'sql' => $query->toSql(),
                        'bindings' => $query->getBindings(),
                        'count' => $query->count(),
                    ]);

                    $dataHarians = $query->get();

                    // Generate PDF
                    $pdf = PDF::loadView('pdf.data-harian', [
                        'dataHarians' => $dataHarians,
                        'period' => $period,
                        'generatedAt' => Carbon::now()->format('d-m-Y H:i:s'),
                    ]);

                    // Buat nama file berdasarkan periode
                    $fileName = 'data-harian-' . str_replace([' ', ':', '/'], '-', strtolower($period)) . '.pdf';
                    
                    // Langsung download file PDF tanpa menyimpan di storage
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $fileName,
                        [
                            'Content-Type' => 'application/pdf',
                        ]
                    );
                }),
        ];
    }
}