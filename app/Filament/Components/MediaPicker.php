<?php

namespace App\Filament\Components;

use App\Models\Media;
use Filament\Forms\Components\Select;

/**
 * Shared "choose from the Media Library" select used by Hero Slides and
 * Gallery. The value is the media's host-relative web path (media.path), so
 * both features reference the same central record without separate uploads.
 */
class MediaPicker
{
    public static function make(string $name, ?string $label = null): Select
    {
        return Select::make($name)
            ->label($label ?? 'Choose from Media Library')
            ->searchable()
            ->allowHtml()
            ->options(function (): array {
                // Browse list shown immediately when the dropdown opens — no
                // typing required. Typing still filters via getSearchResultsUsing.
                return Media::query()
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn (Media $media): array => [
                        $media->path => self::optionLabel($media),
                    ])
                    ->all();
            })
            ->getSearchResultsUsing(function (string $search): array {
                return Media::query()
                    ->search($search)
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn (Media $media): array => [
                        $media->path => self::optionLabel($media),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(function ($value): ?string {
                $media = Media::where('path', $value)->first();

                return $media ? self::optionLabel($media) : e($value);
            })
            // Validation state is a plain string here; normalize to a host-relative path.
            ->mutateStateForValidationUsing(fn ($state): ?string => Media::normalizePath($state))
            ->rules([
                'string',
                'max:255',
                'starts_with:/',
                'not_regex:/\/\//',
                'not_regex:/\.\./',
            ]);
    }

    /**
     * HTML option label for the picker: a thumbnail preview next to the file
     * name — videos get a play badge instead of an image thumb. Values are
     * escaped so allowHtml() cannot be abused via stored names or paths.
     */
    public static function optionLabel(Media $media): string
    {
        if ($media->isVideo()) {
            return '<div class="flex items-center gap-3">'
                .'<span class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 text-primary-500 dark:bg-gray-800">'
                .'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z"/></svg>'
                .'</span>'
                .'<span>'.e($media->name).'</span>'
                .'</div>';
        }

        $url = filled($media->url()) ? url($media->url()) : url('/images/placeholder.png');

        return '<div class="flex items-center gap-3">'
            .'<img src="'.e($url).'" alt="'.e($media->name).'" class="h-10 w-10 rounded object-cover">'
            .'<span>'.e($media->name).'</span>'
            .'</div>';
    }
}
