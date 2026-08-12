<?php

namespace App\Http\Requests\Concerns;

use App\Models\Media;

trait NormalizesMediaPath
{
    /**
     * Merge the normalized (host-relative) form of a media reference field
     * into the request so validation and persistence see a canonical path.
     */
    protected function normalizeMediaPath(string $field): void
    {
        if ($this->has($field)) {
            $this->merge([$field => Media::normalizePath($this->input($field))]);
        }
    }

    /**
     * Validation rules for a media reference field. The stored value must be a
     * host-relative path: a single leading slash, no scheme, no double slash,
     * and no path traversal.
     *
     * @return array<int, mixed>
     */
    protected function mediaPathRules(string $field, bool $nullable = true): array
    {
        $rules = [
            'string',
            'max:255',
            'starts_with:/',
            'not_regex:/\/\//',
            'not_regex:/\.\./',
        ];

        array_unshift($rules, $nullable ? 'nullable' : 'required');

        return $rules;
    }
}
