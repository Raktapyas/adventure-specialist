<?php

namespace App\Filament\Resources\GalleryImageResource\Pages;

use App\Filament\Resources\Concerns\AutoAssignsSortOrder;
use App\Filament\Resources\GalleryImageResource;
use App\Models\GalleryImage;
use App\Models\Media;
use App\Services\MediaUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryImage extends CreateRecord
{
    use AutoAssignsSortOrder;

    protected static string $resource = GalleryImageResource::class;

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
        $data['image_url'] = Media::normalizePath($data['image_url'] ?? null);
        $data = $this->assignSortOrder($data, GalleryImage::class);

        return $data;
    }

    /**
     * Replicate the legacy store() side effect: link the image_url to the
     * media row that owns it.
     */
    protected function afterCreate(): void
    {
        $usage = app(MediaUsageService::class);
        $usage->sync($this->record, 'image_url', $this->record->image_url);
    }
}
