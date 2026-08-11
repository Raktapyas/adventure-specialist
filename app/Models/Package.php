<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'duration_days',
        'cover_image',
        'sort_order',
    ];

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'model');
    }

    public function getPath(): string
    {
        return '/special-package/'.$this->slug.'/';
    }

    public function publicUrl(): string
    {
        return $this->getPath();
    }
}
