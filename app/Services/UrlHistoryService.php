<?php

namespace App\Services;

use App\Models\UrlRedirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UrlHistoryService
{
    /**
     * Persist the redirect rows produced by a model update, atomically with the
     * update itself. Every URL that changes gets an old_path -> resource row so
     * historical links resolve straight to their current owner (one hop, no
     * redirect-to-redirect chains).
     */
    public function update(Model $model, array $data): void
    {
        DB::transaction(function () use ($model, $data) {
            $affected = $this->affectedModels($model);

            $oldPaths = [];

            foreach ($affected as $item) {
                $oldPaths[$item->getKey()] = $item->getPath();
            }

            $model->update($data);

            foreach ($affected as $item) {
                $newPath = $item->fresh()?->getPath();

                if ($newPath === null || $oldPaths[$item->getKey()] === $newPath) {
                    continue;
                }

                UrlRedirect::updateOrCreate(
                    ['old_path' => $oldPaths[$item->getKey()]],
                    [
                        'model_type' => self::typeFor($item) ?? '',
                        'model_id' => $item->getKey(),
                        'new_path' => $newPath,
                    ]
                );
            }
        });
    }

    /**
     * Drop every history row belonging to a resource that is being removed, so
     * its old paths stop resolving (404) instead of pointing at a vanished target.
     */
    public function purge(Model $model): void
    {
        $type = self::typeFor($model);

        if ($type === null) {
            return;
        }

        UrlRedirect::where('model_type', $type)
            ->where('model_id', $model->getKey())
            ->delete();
    }

    /**
     * Find the model currently owning the given (historical, trailing-slash)
     * path, if any.
     */
    public function targetFor(string $path): ?Model
    {
        $redirect = UrlRedirect::where('old_path', $path)->first();

        return $redirect?->target();
    }

    /**
     * Compute the path a model would have if its slug/parent changed, without
     * touching the database. Used by the admin URL preview.
     */
    public static function previewPath(Model $model, ?string $slug = null, $parentId = null): ?string
    {
        if (! method_exists($model, 'getPath')) {
            return null;
        }

        $clone = clone $model;
        $clone->slug = ($slug !== null && $slug !== '') ? $slug : $model->slug;

        if (method_exists($model, 'parent') && $parentId !== null) {
            if ((int) $parentId === (int) $model->getKey()
                || in_array((int) $parentId, $model->descendantIds(), true)) {
                return null;
            }

            $clone->parent_id = $parentId;
            $clone->unsetRelation('parent');
        }

        return $clone->getPath();
    }

    public static function typeFor(Model $model): ?string
    {
        foreach (UrlRedirect::CLASSES as $type => $class) {
            if ($model instanceof $class) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The resource itself plus every descendant whose URL depends on it.
     *
     * @return array<int, Model>
     */
    protected function affectedModels(Model $model): array
    {
        if (! method_exists($model, 'descendantIds')) {
            return [$model];
        }

        $ids = array_merge([$model->getKey()], $model->descendantIds());

        return $model->newQuery()->whereIn($model->getKeyName(), $ids)->get()->all();
    }
}
