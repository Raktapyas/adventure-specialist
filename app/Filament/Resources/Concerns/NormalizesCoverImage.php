<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait NormalizesCoverImage
{
    /**
     * Translate the FileUpload's disk-relative state into the web path
     * convention stored in the database.
     */
    protected function normalizeCoverImage(?string $path): ?string
    {
        $path = Media::normalizePath($path);

        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.$path;
    }

    /**
     * FileUpload bound to the existing cover_image column. State stays
     * disk-relative (uploads/...) while the database keeps the web path
     * convention (/storage/...). The hydration override strips the /storage/
     * prefix for preview and deliberately does NOT filter by disk existence so
     * legacy /assets/... paths round-trip untouched.
     */
    protected static function coverImageField(): FileUpload
    {
        return FileUpload::make('cover_image')
            ->label('Cover image')
            ->image()
            ->disk('public')
            ->directory('uploads')
            ->visibility('public')
            ->imagePreviewHeight('200')
            ->helperText('Upload a new image, or leave as-is to keep the current one.')
            ->afterStateHydrated(function (FileUpload $component, string|array|null $state): void {
                if (blank($state)) {
                    $component->state([]);

                    return;
                }

                $files = collect(Arr::wrap($state))
                    ->mapWithKeys(function (string $file): array {
                        $file = str_starts_with($file, '/storage/')
                            ? substr($file, strlen('/storage/'))
                            : $file;

                        return [(string) Str::uuid() => $file];
                    })
                    ->all();

                $component->state($files);
            });
    }
}
