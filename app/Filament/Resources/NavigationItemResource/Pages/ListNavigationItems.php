<?php

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Filament\Resources\NavigationItemResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Schema;

class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    public function mount(): void
    {
        parent::mount();

        if (! Schema::hasTable('navigation_items')) {
            Notification::make()
                ->title('Navigation table missing')
                ->body('The navigation_items table does not exist yet. Please run: php artisan migrate (or ./vendor/bin/sail artisan migrate if using Sail/Docker).')
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn (): bool => Schema::hasTable('navigation_items')),
        ];
    }
}
