<?php

namespace App\Filament\Resources\GalleryImageResource\Pages;

use App\Filament\Resources\GalleryImageResource;
use App\Models\GalleryImage;
use App\Models\Media;
use App\Services\MediaUsageService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGalleryImage extends EditRecord
{
    protected static string $resource = GalleryImageResource::class;

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
        $data['image_url'] = Media::normalizePath($data['image_url'] ?? null);

        return $data;
    }

    /**
     * Replicate the legacy update() side effect: link the image_url to the
     * media row that owns it.
     */
    protected function afterSave(): void
    {
        $usage = app(MediaUsageService::class);
        $usage->sync($this->record, 'image_url', $this->record->image_url);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (GalleryImage $record): void {
                    app(MediaUsageService::class)->purgeModel($record);
                }),
        ];
    }
}
