<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Media;
use App\Services\MediaUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
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
        $data['cover_image'] = Media::normalizePath($data['cover_image'] ?? null);

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
