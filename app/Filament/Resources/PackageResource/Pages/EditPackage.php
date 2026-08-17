<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\Concerns\NormalizesCoverImage;
use App\Filament\Resources\PackageResource;
use App\Models\Package;
use App\Services\MediaUsageService;
use App\Services\UrlHistoryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPackage extends EditRecord
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
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['cover_image'] = $this->normalizeCoverImage($data['cover_image'] ?? null);

        return $data;
    }

    /**
     * Persist the update through UrlHistoryService so old->new redirects are
     * recorded atomically with the update itself. The service calls
     * $record->update($data) inside its own DB transaction, so we must not
     * update the record separately here.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UrlHistoryService::class)->update($record, $data);

        return $record;
    }

    /**
     * Replicate the legacy update() side effects: link the cover image and any
     * media referenced inside the content to this package.
     */
    protected function afterSave(): void
    {
        $usage = app(MediaUsageService::class);
        $usage->sync($this->record, 'cover_image', $this->record->cover_image);
        $usage->syncContent($this->record, $this->record->content);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (Package $record): void {
                    app(UrlHistoryService::class)->purge($record);
                    app(MediaUsageService::class)->purgeModel($record);
                }),
        ];
    }
}
