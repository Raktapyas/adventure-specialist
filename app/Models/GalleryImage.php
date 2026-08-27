<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'caption',
        'sort_order',
    ];

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaUsage::class, 'model');
    }

    /**
     * The central Media Library record this gallery item references by web path.
     */
    public function media(): HasOne
    {
        return $this->hasOne(Media::class, 'path', 'image_url');
    }

    /**
     * Whether the referenced media is a video (defaults to image for legacy
     * rows without a matching Media record).
     */
    public function isVideo(): bool
    {
        return $this->media?->isVideo() ?? false;
    }
}
