<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
