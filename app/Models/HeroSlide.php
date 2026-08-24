<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'eyebrow',
        'title',
        'lede',
        'primary_cta_label',
        'primary_cta_url',
        'secondary_cta_label',
        'secondary_cta_url',
        'effect',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Shape expected by the <x-hero> slider component.
     *
     * @return array{image: string, eyebrow: ?string, title: string, lede: ?string, kenburns: string, ctas: array<int, array{label: string, href: string, style: string}>}
     */
    public function toSlide(): array
    {
        $ctas = [];

        if (filled($this->primary_cta_label) && filled($this->primary_cta_url)) {
            $ctas[] = ['label' => $this->primary_cta_label, 'href' => $this->primary_cta_url, 'style' => 'royal'];
        }

        if (filled($this->secondary_cta_label) && filled($this->secondary_cta_url)) {
            $ctas[] = ['label' => $this->secondary_cta_label, 'href' => $this->secondary_cta_url, 'style' => 'outline'];
        }

        return [
            'image' => $this->image_path,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'lede' => $this->lede,
            'kenburns' => $this->effect ?: 'animate-hero-zoom-in',
            'ctas' => $ctas,
        ];
    }
}
