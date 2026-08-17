<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Media;
use App\Models\Page;
use App\Services\MediaUsageService;
use App\Services\UrlHistoryService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPage extends EditRecord
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
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['cover_image'] = Media::normalizePath($data['cover_image'] ?? null);

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
     * media referenced inside the content to this page.
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
                ->before(function (Page $record, Actions\DeleteAction $action): void {
                    if ($record->children()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Cannot delete')
                            ->body('This page has child pages. Move or delete them first.')
                            ->send();

                        $action->halt();
                    }
                })
                ->after(function (Page $record): void {
                    app(UrlHistoryService::class)->purge($record);
                    app(MediaUsageService::class)->purgeModel($record);
                }),
        ];
    }
}
