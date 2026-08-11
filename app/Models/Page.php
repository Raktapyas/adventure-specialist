<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'excerpt',
        'content',
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
        if ($this->slug === 'managing-director') {
            return '/contact/managing-director/';
        }

        if ($this->slug === 'about') {
            return '/about-us/';
        }

        return '/about-us/'.$this->slug.'/';
    }

    public function publicUrl(): string
    {
        return $this->getPath();
    }
}
