<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InquiryResource\Pages\ListInquiries;
use App\Models\Destination;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Total Inquiries', Inquiry::count())
                ->color('primary')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->description('New submissions this week')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 12, 10, 15, 20, 18, 26]),
            Stat::make('Active Packages', Package::published()->count())
                ->color('success')
                ->icon('heroicon-m-map')
                ->description('Active offerings')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([10, 12, 14, 13, 16, 19, 22]),
            Stat::make('Destinations', Destination::count())
                ->color('warning')
                ->icon('heroicon-m-globe-alt')
                ->description('Explore locations')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([6, 7, 8, 10, 11, 13, 15]),
            Stat::make('Registered Users', User::count())
                ->color('info')
                ->icon('heroicon-m-users')
                ->description('User accounts')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart([3, 4, 5, 5, 7, 9, 11]),
        ];

        if (Inquiry::query()->unread()->exists()) {
            $stats[] = Stat::make('Unread Inquiries', Inquiry::query()->unread()->count())
                ->color('danger')
                ->icon('heroicon-m-envelope-open')
                ->description('Require your attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->chart([8, 6, 9, 7, 11, 9, 12])
                ->url(ListInquiries::getUrl());
        }

        return $stats;
    }
}
