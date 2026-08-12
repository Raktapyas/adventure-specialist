<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MediaUsageService
{
    /**
     * Point the "field" usage of a model at the media row owning the given
     * web path (when one exists), dropping any previous value for that field.
     *
     * The path is normalized to its host-relative form first, so an absolute
     * URL and its host-relative path reference the same media row.
     */
    public function sync(Model $model, string $field, ?string $path): void
    {
        $this->clear($model, $field);

        $path = Media::normalizePath($path);

        if ($path === null || $path === '') {
            return;
        }

        $media = Media::where('path', $path)->first();

        if ($media === null) {
            return;
        }

        $this->add($media, $model, $field);
    }

    public function add(Media $media, Model $model, string $field): void
    {
        MediaUsage::updateOrCreate([
            'media_id' => $media->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'field' => $field,
        ]);
    }

    public function clear(Model $model, string $field): void
    {
        MediaUsage::where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->where('field', $field)
            ->delete();
    }

    /**
     * Remove every usage row pointing at a model (used on model delete).
     */
    public function purgeModel(Model $model): void
    {
        MediaUsage::where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->delete();
    }

    /**
     * Re-link the media rows referenced inside a model's raw HTML content.
     *
     * This is the destructive form used when content is actually replaced
     * (admin store/update): existing content usages are cleared and rebuilt
     * from the new HTML. It must not be used for maintenance scans.
     */
    public function syncContent(Model $model, ?string $html): void
    {
        $this->clear($model, 'content');

        if ($html === null || $html === '') {
            return;
        }

        foreach ($this->extractImagePaths($html) as $path) {
            $media = Media::where('path', $path)->first();

            if ($media !== null) {
                $this->add($media, $model, 'content');
            }
        }
    }

    /**
     * Add usage rows for every media path referenced inside a model's raw
     * HTML content. Strictly additive: existing content usages are never
     * removed, even when the scanner cannot detect their reference (e.g. CSS
     * background URLs, srcset, escaped or relative paths).
     */
    public function scanContentFor(Model $model, ?string $html): void
    {
        if ($html === null || $html === '') {
            return;
        }

        foreach ($this->extractImagePaths($html) as $path) {
            $media = Media::where('path', $path)->first();

            if ($media !== null) {
                $this->add($media, $model, 'content');
            }
        }
    }

    /**
     * Scan every model's raw HTML content for <img src> paths and link the
     * matching media rows. Additive only: existing content usages are never
     * removed, so references the scanner cannot see (CSS background URLs,
     * srcset, escaped or relative paths) are preserved.
     */
    public function scanContent(): array
    {
        $models = [
            Page::class,
            Service::class,
            Destination::class,
            Package::class,
        ];

        $linked = 0;

        DB::transaction(function () use ($models, &$linked) {
            foreach ($models as $class) {
                foreach ($class::whereNotNull('content')->cursor() as $model) {
                    $before = MediaUsage::where('model_type', $model->getMorphClass())
                        ->where('model_id', $model->getKey())
                        ->where('field', 'content')
                        ->count();

                    $this->scanContentFor($model, $model->content);

                    $after = MediaUsage::where('model_type', $model->getMorphClass())
                        ->where('model_id', $model->getKey())
                        ->where('field', 'content')
                        ->count();

                    $linked += max(0, $after - $before);
                }
            }
        });

        return ['linked' => $linked];
    }

    /**
     * @return list<string>
     */
    protected function extractImagePaths(string $html): array
    {
        preg_match_all('/src=["\']([^"\']+)["\']/i', $html, $matches);

        $paths = [];

        foreach ($matches[1] ?? [] as $src) {
            $path = Media::normalizePath($src);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }
}
