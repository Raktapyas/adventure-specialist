<?php

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\HeroSlideResource;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Services\MediaUsageService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeroSlide extends EditRecord
{
    protected static string $resource = HeroSlideResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['image_path'] = Media::normalizePath($data['image_path'] ?? null);

        return $data;
    }

    /**
     * Re-link the image_path to the media row that owns it after every save.
     */
    protected function afterSave(): void
    {
        app(MediaUsageService::class)->sync($this->record, 'image_path', $this->record->image_path);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (HeroSlide $record): void {
                    app(MediaUsageService::class)->purgeModel($record);
                }),
        ];
    }
}
