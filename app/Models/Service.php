<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'icon',
        'cover_image',
        'sort_order',
        'is_published',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'model');
    }

    public function descendantIds(): array
    {
        return $this->children()->pluck('id')->flatMap(function (int $id) {
            return array_merge([$id], static::find($id)?->descendantIds() ?? []);
        })->all();
    }

    public function chainDepth(): int
    {
        $depth = 0;

        for ($current = $this->parent; $current; $current = $current->parent) {
            $depth++;
        }

        return $depth;
    }

    public function getPath(): string
    {
        return '/ast-services/'.implode('/', $this->slugChain()).'/';
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

            $current = $query->published()->first();

            if (! $current) {
                return null;
            }
        }

        return $current;
    }
}
