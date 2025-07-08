<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Harga per Komoditas - Hari Ini';
    
    // Chart takes full width
    protected int | string | array $columnSpan = 'full';
    
    // Chart height
    protected static ?string $maxHeight = '400px';
    
    // Refresh interval (optional)
    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $today = Carbon::today();
        
        // Get all active commodities
        $komoditasList = Komoditas::orderBy('name')->get();
        
        if ($komoditasList->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Color palette for different commodities
        $colors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#6b7280',
            '#14b8a6', '#f43f5e', '#059669', '#dc2626', '#7c3aed'
        ];

        $datasets = [];
        $labels = [];
        $colorIndex = 0;

        foreach ($komoditasList as $komoditas) {
            // Get today's data for this commodity (only active status)
            $dataHarian = DataHarian::where('komoditas_id', $komoditas->id)
                ->whereDate('created_at', $today)
                ->where('status', true)
                ->orderBy('created_at')
                ->get();

            if ($dataHarian->isNotEmpty()) {
                $komoditasData = [];
                $timeLabels = [];
                
                foreach ($dataHarian as $data) {
                    $komoditasData[] = (float) $data->data_input;
                    $timeLabels[] = $data->created_at->format('H:i');
                }

                // Use time labels from the first commodity with data
                if (empty($labels)) {
                    $labels = $timeLabels;
                }

                // Create dataset for this commodity
                $color = $colors[$colorIndex % count($colors)];
                $datasets[] = [
                    'label' => $komoditas->name,
                    'data' => $komoditasData,
                    'borderColor' => $color,
                    'backgroundColor' => $color . '20',
                    'tension' => 0.4,
                    'fill' => false,
                    'pointBackgroundColor' => $color,
                    'pointBorderColor' => $color,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBorderWidth' => 2,
                ];

                $colorIndex++;
            }
        }

        // If no time-based data available, show average prices
        if (empty($datasets)) {
            $averageData = [];
            $commodityLabels = [];
            
            foreach ($komoditasList as $komoditas) {
                $average = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->where('status', true)
                    ->avg('data_input');

                if ($average !== null) {
                    $commodityLabels[] = $komoditas->name;
                    $averageData[] = round($average, 2);
                }
            }
            
            if (!empty($averageData)) {
                $labels = $commodityLabels;
                $datasets[] = [
                    'label' => 'Rata-rata Harga Hari Ini',
                    'data' => $averageData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#3b82f6',
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'pointBorderWidth' => 2,
                ];
            }
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

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 2.5,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold'
                        ]
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                        'borderDash' => [5, 5],
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 12
                        ],
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString(); }'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu / Komoditas',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold'
                        ]
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 12
                        ],
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ]
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 12,
                            'weight' => '500'
                        ],
                        'padding' => 20,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ]
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold'
                    ],
                    'bodyFont' => [
                        'size' => 12
                    ],
                    'padding' => 12,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString(); }'
                    ]
                ],
                'title' => [
                    'display' => false,
                ]
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 8,
                    'borderWidth' => 2,
                ],
                'line' => [
                    'borderWidth' => 3,
                    'borderCapStyle' => 'round',
                    'borderJoinStyle' => 'round',
                ]
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'layout' => [
                'padding' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ]
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeInOutQuart',
            ]
        ];
    }
}