<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
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
