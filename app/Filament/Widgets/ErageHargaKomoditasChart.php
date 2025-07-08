<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DataHarian;
use App\Models\Komoditas;
use Carbon\Carbon;

class ErageHargaKomoditasChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Harga Komoditas - 7 Hari Terakhir';
    
    // Chart takes full width
    protected int | string | array $columnSpan = 'full';
    
    // Chart height
    protected static ?string $maxHeight = '500px';
    
    // Refresh interval
    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        // Get data from last 7 days
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Get all active commodities with data in the last 7 days
        $komoditasList = Komoditas::whereHas('dataHarian', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 1); // Only active data
        })->orderBy('name')->get();
        
        if ($komoditasList->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Create date labels for the last 7 days
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('Y-m-d');
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
            
            // Get data for each day in the last 7 days
            foreach ($labels as $date) {
                // Get average price for this commodity on this date (only active data)
                $avgPrice = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $date)
                    ->where('status', 1)
                    ->avg('data_input');
                
                $komoditasData[] = $avgPrice ? round($avgPrice, 0) : null;
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
                    'spanGaps' => true, // Connect lines even if there are null values
                ];

                $colorIndex++;
            }
        }

        // Convert date labels to more readable format
        $formattedLabels = array_map(function($date) {
            return Carbon::parse($date)->format('d/m');
        }, $labels);

        return [
            'datasets' => $datasets,
            'labels' => $formattedLabels,
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
                    'beginAtZero' => false, // Don't start from zero to show price variations better
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp/Kg)',
                        'font' => [
                            'size' => 14,
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
                            'size' => 11,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'padding' => 8,
                        'callback' => 'function(value) { return value + " rb"; }'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal',
                        'font' => [
                            'size' => 14,
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
                            'size' => 11,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'maxRotation' => 0,
                        'minRotation' => 0,
                        'padding' => 8,
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
                            'size' => 12,
                            'weight' => '500',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151',
                        'padding' => 15,
                        'usePointStyle' => true,
                        'pointStyle' => 'line',
                        'boxWidth' => 20,
                        'boxHeight' => 2,
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'titleFont' => [
                        'size' => 13,
                        'weight' => 'bold',
                        'family' => 'Inter, system-ui, sans-serif'
                    ],
                    'titleColor' => '#ffffff',
                    'bodyFont' => [
                        'size' => 12,
                        'family' => 'Inter, system-ui, sans-serif'
                    ],
                    'bodyColor' => '#e5e7eb',
                    'padding' => 12,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'borderColor' => 'rgba(107, 114, 128, 0.3)',
                    'borderWidth' => 1,
                    'caretSize' => 6,
                    'caretPadding' => 8,
                    'filter' => 'function(tooltipItem) { return tooltipItem.parsed.y !== null; }',
                    'callbacks' => [
                        'title' => 'function(context) { 
                            const date = context[0].label;
                            const fullDate = new Date();
                            const [day, month] = date.split("/");
                            fullDate.setDate(parseInt(day));
                            fullDate.setMonth(parseInt(month) - 1);
                            return fullDate.toLocaleDateString("id-ID", { weekday: "long", day: "numeric", month: "long" });
                        }',
                        'label' => 'function(context) { 
                            if (context.parsed.y === null) return null;
                            return context.dataset.label + ": Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y) + "/Kg"; 
                        }'
                    ]
                ],
                'title' => [
                    'display' => false,
                ]
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
                ],
                'line' => [
                    'borderWidth' => 2.5,
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