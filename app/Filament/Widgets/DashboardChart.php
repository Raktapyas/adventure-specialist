<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class DashboardChart extends ChartWidget
{
    protected static ?string $heading = 'Inquiry & Lead Velocity';

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 4];

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset): string => now()->subMonths($offset)->format('Y-m'));

        $counts = Inquiry::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $data = $months->map(fn (string $month): int => (int) ($counts[$month] ?? 0));
        $labels = $months->map(
            fn (string $month): string => Carbon::createFromFormat('Y-m', $month)->format('M Y')
        );

        return [
            'datasets' => [
                [
                    'label' => 'Inquiries',
                    'data' => $data->values()->all(),
                    'fill' => true,
                    'tension' => 0.4,
                    'backgroundColor' => 'linear-gradient(180deg, rgba(14, 165, 233, 0.35) 0%, rgba(14, 165, 233, 0.02) 100%)',
                    'borderColor' => 'rgb(14, 165, 233)',
                ],
            ],
            'labels' => $labels->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
