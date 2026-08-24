<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    protected $model = HeroSlide::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image_path' => '/assets/images/banners/'.fake()->numberBetween(1, 3).'.jpg',
            'eyebrow' => fake()->company(),
            'title' => fake()->sentence(4),
            'lede' => fake()->sentence(),
            'primary_cta_label' => 'Explore',
            'primary_cta_url' => '/ast-services/',
            'secondary_cta_label' => 'Plan a Trip',
            'secondary_cta_url' => '/contact/#enquiry',
            'effect' => 'animate-hero-zoom-in',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_published' => true,
        ];
    }
}
