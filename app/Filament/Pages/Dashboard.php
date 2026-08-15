<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardChart;
use App\Filament\Widgets\RecentInquiriesOverview;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            DashboardChart::class,
            RecentInquiriesOverview::class,
        ];
    }
}
