<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Harga Komoditas - Hari Ini';
    
    // Chart takes full width
    protected int | string | array $columnSpan = 'full';
    
    // No height limit - let it expand
    protected static ?string $maxHeight = null;
    
    // Refresh interval
    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $today = Carbon::today();
        
        // Get all active commodities that have data today
        $komoditasList = Komoditas::whereHas('dataHarian', function($query) use ($today) {
            $query->whereDate('created_at', $today)
                  ->where('status', 1); // Only active data
        })->orderBy('name')->get();
        
        if ($komoditasList->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Get all unique time points for today (sorted)
        $timePoints = DataHarian::whereDate('created_at', $today)
            ->where('status', 1)
            ->orderBy('created_at')
            ->pluck('created_at')
            ->map(function($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->unique()
            ->values()
            ->toArray();

        if (empty($timePoints)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Modern color palette with better contrast
        $colors = [
            ['border' => '#3b82f6', 'background' => 'rgba(59, 130, 246, 0.1)'], // Blue
            ['border' => '#f59e0b', 'background' => 'rgba(245, 158, 11, 0.1)'], // Orange
            ['border' => '#ec4899', 'background' => 'rgba(236, 72, 153, 0.1)'], // Pink
            ['border' => '#10b981', 'background' => 'rgba(16, 185, 129, 0.1)'], // Green
            ['border' => '#8b5cf6', 'background' => 'rgba(139, 92, 246, 0.1)'], // Purple
            ['border' => '#ef4444', 'background' => 'rgba(239, 68, 68, 0.1)'], // Red
            ['border' => '#06b6d4', 'background' => 'rgba(6, 182, 212, 0.1)'], // Cyan
            ['border' => '#84cc16', 'background' => 'rgba(132, 204, 22, 0.1)'], // Lime
            ['border' => '#f97316', 'background' => 'rgba(249, 115, 22, 0.1)'], // Orange-red
            ['border' => '#14b8a6', 'background' => 'rgba(20, 184, 166, 0.1)'], // Teal
        ];

        $datasets = [];
        $colorIndex = 0;

        foreach ($komoditasList as $komoditas) {
            $komoditasData = [];
            
            // Get data for each time point
            foreach ($timePoints as $time) {
                // Get data for this commodity at this time
                $data = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->whereTime('created_at', 'like', $time . '%')
                    ->where('status', 1)
                    ->first();
                
                $komoditasData[] = $data ? (float) $data->data_input : null;
            }

            // Only add dataset if there's at least one data point
            if (array_filter($komoditasData, function($value) { return $value !== null; })) {
                $color = $colors[$colorIndex % count($colors)];
                
                $datasets[] = [
                    'label' => $komoditas->name,
                    'data' => $komoditasData,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['background'],
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => $color['border'],
                    'pointBorderColor' => '#ffffff',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBorderWidth' => 2,
                    'pointHoverBorderWidth' => 3,
                    'borderWidth' => 2.5,
                    'spanGaps' => true,
                ];

                $colorIndex++;
            }
        }

        return [
            'datasets' => $datasets,
            'labels' => $timePoints,
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
            'aspectRatio' => 3.5, // Wider aspect ratio for more horizontal space
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp/Kg)',
                        'font' => [
                            'size' => 16,
                            'weight' => 'bold',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151'
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(107, 114, 128, 0.15)',
                        'borderDash' => [2, 4],
                        'lineWidth' => 1,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 13,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'padding' => 10,
                        'callback' => 'function(value) { return new Intl.NumberFormat("id-ID").format(value); }'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu',
                        'font' => [
                            'size' => 16,
                            'weight' => 'bold',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151'
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(107, 114, 128, 0.1)',
                        'lineWidth' => 1,
                    ],
                    'border' => [
                        'color' => 'rgba(107, 114, 128, 0.2)',
                        'width' => 1,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 13,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'maxRotation' => 45,
                        'minRotation' => 0,
                        'padding' => 10,
                    ]
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'align' => 'start',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                            'weight' => '500',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151',
                        'padding' => 20,
                        'usePointStyle' => true,
                        'pointStyle' => 'line',
                        'boxWidth' => 25,
                        'boxHeight' => 3,
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold',
                        'family' => 'Inter, system-ui, sans-serif'
                    ],
                    'titleColor' => '#ffffff',
                    'bodyFont' => [
                        'size' => 13,
                        'family' => 'Inter, system-ui, sans-serif'
                    ],
                    'bodyColor' => '#e5e7eb',
                    'padding' => 15,
                    'cornerRadius' => 10,
                    'displayColors' => true,
                    'borderColor' => 'rgba(107, 114, 128, 0.3)',
                    'borderWidth' => 1,
                    'caretSize' => 8,
                    'caretPadding' => 10,
                    'filter' => 'function(tooltipItem) { return tooltipItem.parsed.y !== null; }',
                    'callbacks' => [
                        'title' => 'function(context) { 
                            return "Pukul " + context[0].label;
                        }',
                        'label' => 'function(context) { 
                            if (context.parsed.y === null) return null;
                            return context.dataset.label + ": Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y); 
                        }'
                    ]
                ],
                'title' => [
                    'display' => false,
                ]
            ],
            'elements' => [
                'point' => [
                    'radius' => 5,
                    'hoverRadius' => 8,
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
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
                    'top' => 30,
                    'right' => 30,
                    'bottom' => 30,
                    'left' => 30,
                ]
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeInOutQuart',
            ]
        ];
    }
}