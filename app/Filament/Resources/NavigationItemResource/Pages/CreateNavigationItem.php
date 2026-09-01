<?php

namespace App\Filament\Resources\NavigationItemResource\Pages;

use App\Filament\Resources\Concerns\AutoAssignsSortOrder;
use App\Filament\Resources\NavigationItemResource;
use App\Models\NavigationItem;
use Filament\Resources\Pages\CreateRecord;

class CreateNavigationItem extends CreateRecord
{
    use AutoAssignsSortOrder;

    protected static string $resource = NavigationItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['type'] ?? null) === 'dropdown') {
            $data['url'] = null;
        }

        if (($data['url'] ?? null) === '#') {
            $data['type'] = 'dropdown';
            $data['url'] = null;
        }

        return $this->assignSortOrder($data, NavigationItem::class);
    }
}
