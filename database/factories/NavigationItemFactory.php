<?php

namespace Database\Factories;

use App\Models\NavigationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationItem>
 */
class NavigationItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'url' => '/'.fake()->slug().'/',
            'type' => 'custom',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => true,
            'open_in_new_tab' => false,
            'parent_id' => null,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attrs) => ['is_visible' => false]);
    }

    public function external(): static
    {
        return $this->state(fn (array $attrs) => [
            'url' => fake()->url(),
            'open_in_new_tab' => true,
        ]);
    }
}
