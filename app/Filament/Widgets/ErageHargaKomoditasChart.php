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
    
    // Chart height - increased for better visibility
    protected static ?string $maxHeight = '500px';
    
    // Refresh interval
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

        // Modern gradient color palette
        $colors = [
            ['border' => '#6366f1', 'background' => 'rgba(99, 102, 241, 0.1)', 'gradient' => 'rgba(99, 102, 241, 0.3)'],
            ['border' => '#ec4899', 'background' => 'rgba(236, 72, 153, 0.1)', 'gradient' => 'rgba(236, 72, 153, 0.3)'],
            ['border' => '#10b981', 'background' => 'rgba(16, 185, 129, 0.1)', 'gradient' => 'rgba(16, 185, 129, 0.3)'],
            ['border' => '#f59e0b', 'background' => 'rgba(245, 158, 11, 0.1)', 'gradient' => 'rgba(245, 158, 11, 0.3)'],
            ['border' => '#8b5cf6', 'background' => 'rgba(139, 92, 246, 0.1)', 'gradient' => 'rgba(139, 92, 246, 0.3)'],
            ['border' => '#06b6d4', 'background' => 'rgba(6, 182, 212, 0.1)', 'gradient' => 'rgba(6, 182, 212, 0.3)'],
            ['border' => '#f97316', 'background' => 'rgba(249, 115, 22, 0.1)', 'gradient' => 'rgba(249, 115, 22, 0.3)'],
            ['border' => '#84cc16', 'background' => 'rgba(132, 204, 22, 0.1)', 'gradient' => 'rgba(132, 204, 22, 0.3)'],
            ['border' => '#ef4444', 'background' => 'rgba(239, 68, 68, 0.1)', 'gradient' => 'rgba(239, 68, 68, 0.3)'],
            ['border' => '#14b8a6', 'background' => 'rgba(20, 184, 166, 0.1)', 'gradient' => 'rgba(20, 184, 166, 0.3)'],
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

                // Create dataset for this commodity with modern styling
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
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'pointBorderWidth' => 3,
                    'pointHoverBorderWidth' => 4,
                    'borderWidth' => 3,
                    'pointHoverBackgroundColor' => $color['border'],
                    'pointHoverBorderColor' => '#ffffff',
                ];

                $colorIndex++;
            }
        }

        // If no time-based data available, show average prices with bar chart style
        if (empty($datasets)) {
            $averageData = [];
            $commodityLabels = [];
            $backgroundColors = [];
            $borderColors = [];
            
            foreach ($komoditasList as $index => $komoditas) {
                $average = DataHarian::where('komoditas_id', $komoditas->id)
                    ->whereDate('created_at', $today)
                    ->where('status', true)
                    ->avg('data_input');

                if ($average !== null) {
                    $commodityLabels[] = $komoditas->name;
                    $averageData[] = round($average, 2);
                    
                    $color = $colors[$index % count($colors)];
                    $backgroundColors[] = $color['gradient'];
                    $borderColors[] = $color['border'];
                }
            }
            
            if (!empty($averageData)) {
                $labels = $commodityLabels;
                $datasets[] = [
                    'label' => 'Rata-rata Harga Hari Ini',
                    'data' => $averageData,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
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
        // Check if we have time-based data or average data
        $today = Carbon::today();
        $hasTimeData = false;
        
        $komoditasList = Komoditas::orderBy('name')->get();
        foreach ($komoditasList as $komoditas) {
            $dataCount = DataHarian::where('komoditas_id', $komoditas->id)
                ->whereDate('created_at', $today)
                ->where('status', true)
                ->count();
            
            if ($dataCount > 1) {
                $hasTimeData = true;
                break;
            }
        }
        
        return $hasTimeData ? 'line' : 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 2.2,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Harga (Rp)',
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
                        'borderDash' => [2, 4],
                        'lineWidth' => 1,
                    ],
                    'border' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'padding' => 8,
                        'callback' => 'function(value) { return "Rp " + new Intl.NumberFormat("id-ID").format(value); }'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Waktu / Komoditas',
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151'
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'color' => 'rgba(107, 114, 128, 0.2)',
                        'width' => 1,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#6b7280',
                        'maxRotation' => 45,
                        'minRotation' => 0,
                        'padding' => 8,
                    ]
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'start',
                    'labels' => [
                        'font' => [
                            'size' => 13,
                            'weight' => '500',
                            'family' => 'Inter, system-ui, sans-serif'
                        ],
                        'color' => '#374151',
                        'padding' => 20,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 8,
                        'boxHeight' => 8,
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
                    'padding' => 16,
                    'cornerRadius' => 12,
                    'displayColors' => true,
                    'borderColor' => 'rgba(107, 114, 128, 0.2)',
                    'borderWidth' => 1,
                    'caretSize' => 8,
                    'caretPadding' => 12,
                    'callbacks' => [
                        'label' => 'function(context) { 
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
                    'radius' => 6,
                    'hoverRadius' => 8,
                    'borderWidth' => 3,
                    'hoverBorderWidth' => 4,
                ],
                'line' => [
                    'borderWidth' => 3,
                    'borderCapStyle' => 'round',
                    'borderJoinStyle' => 'round',
                ],
                'bar' => [
                    'borderRadius' => 8,
                    'borderSkipped' => false,
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
                'duration' => 1200,
                'easing' => 'easeInOutCubic',
            ]
        ];
    }
}