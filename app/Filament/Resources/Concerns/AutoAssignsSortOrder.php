<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Auto-assign the next sort_order on create when the field is left blank.
 * Keeps manual overrides intact (0 and explicit values are preserved).
 */
trait AutoAssignsSortOrder
{
    /**
     * @param  array<string, mixed>  $data
     * @param  class-string<Model>  $modelClass
     */
    protected function assignSortOrder(array $data, string $modelClass): array
    {
        if (! array_key_exists('sort_order', $data) || blank($data['sort_order'])) {
            $max = $modelClass::query()->max('sort_order');

            $data['sort_order'] = $max === null ? 0 : ((int) $max + 1);
        }

        return $data;
    }
}
