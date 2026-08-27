<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\Concerns\AutoAssignsSortOrder;
use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\PageResource;
use App\Models\Page;
use App\Services\MediaUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use AutoAssignsSortOrder;
    use NormalizesCoverImage;

    protected static string $resource = PageResource::class;

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
        $data['cover_image'] = $this->normalizeCoverImage($data['cover_image'] ?? null);
        $data = $this->assignSortOrder($data, Page::class);

        return $data;
    }

    /**
     * Replicate the legacy store() side effects: link the cover image and any
     * media referenced inside the content to this page.
     */
    protected function afterCreate(): void
    {
        $usage = app(MediaUsageService::class);
        $usage->sync($this->record, 'cover_image', $this->record->cover_image);
        $usage->syncContent($this->record, $this->record->content);
    }
}
