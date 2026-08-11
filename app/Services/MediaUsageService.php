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
     */
    public function sync(Model $model, string $field, ?string $path): void
    {
        $this->clear($model, $field);

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
     * Scan every model's raw HTML content for <img src> paths and link the
     * matching media rows. Existing content usages are refreshed, never
     * removed for references this scanner cannot see.
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

                    $this->syncContent($model, $model->content);

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

        return array_values(array_unique(array_map(
            fn (string $src) => $src,
            $matches[1] ?? []
        )));
    }
}
