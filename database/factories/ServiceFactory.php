<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(2),
            'excerpt' => fake()->sentence(),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'icon' => fake()->randomElement([
                'heroicon-o-paper-airplane',
                'heroicon-o-map',
                'heroicon-o-sun',
                'heroicon-o-fire',
                'heroicon-o-rocket-launch',
                'heroicon-o-photo',
            ]),
            'cover_image' => '/assets/images/'.fake()->slug(2).'.jpg',
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
