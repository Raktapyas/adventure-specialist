<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function getPath(): string
    {
        $chain = $this->slugChain();
        $root = $chain[0] ?? $this->slug;

        if ($root === 'nepal') {
            $rest = implode('/', array_slice($chain, 1));

            return $rest === '' ? '/nepal/' : '/nepal/'.$rest.'/';
        }

        return '/destination/'.implode('/', $chain).'/';
    }

    public function publicUrl(): string
    {
        return $this->getPath();
    }

    protected function slugChain(): array
    {
        $chain = [];

        for ($current = $this; $current; $current = $current->parent) {
            array_unshift($chain, $current->slug);
        }

        return $chain;
    }

    public static function resolvePath(array $segments): ?self
    {
        if (empty($segments)) {
            return null;
        }

        $current = null;

        foreach ($segments as $slug) {
            $query = static::query()->where('slug', $slug);

            $query = $current
                ? $query->where('parent_id', $current->id)
                : $query->whereNull('parent_id');

            $current = $query->first();

            if (! $current) {
                return null;
            }
        }

        return $current;
    }
}
