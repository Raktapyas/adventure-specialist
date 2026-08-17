<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\PackageResource;
use App\Services\MediaUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    use NormalizesCoverImage;

    protected static string $resource = PackageResource::class;

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

        return $data;
    }

    /**
     * Replicate the legacy store() side effects: link the cover image and any
     * media referenced inside the content to this package.
     */
    protected function afterCreate(): void
    {
        $usage = app(MediaUsageService::class);
        $usage->sync($this->record, 'cover_image', $this->record->cover_image);
        $usage->syncContent($this->record, $this->record->content);
    }
}
