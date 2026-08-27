<?php

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\Concerns\AutoAssignsSortOrder;
use App\Filament\Resources\HeroSlideResource;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Services\MediaUsageService;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroSlide extends CreateRecord
{
    use AutoAssignsSortOrder;

    protected static string $resource = HeroSlideResource::class;

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
        $data['image_path'] = Media::normalizePath($data['image_path'] ?? null);
        $data = $this->assignSortOrder($data, HeroSlide::class);

        return $data;
    }

    /**
     * Link the image_path to the media row that owns it so deletion guards
     * see the reference (same contract as Gallery).
     */
    protected function afterCreate(): void
    {
        app(MediaUsageService::class)->sync($this->record, 'image_path', $this->record->image_path);
    }
}
