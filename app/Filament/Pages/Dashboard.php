<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SurveyorCount;
use App\Filament\Widgets\KomoditasCount;
use App\Filament\Widgets\DataHarianCount;
use App\Filament\Widgets\RespondenCount;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $homeUrl = '/admin';

    protected function getHeaderWidgets(): array
    {
        return [];
    }
    

}