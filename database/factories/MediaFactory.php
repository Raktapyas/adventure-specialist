<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->slug(2).'.jpg';

        return [
            'name' => $name,
            'path' => '/assets/images/'.$name,
            'disk' => null,
            'storage_path' => null,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => fake()->numberBetween(10_000, 500_000),
            'alt_text' => fake()->sentence(),
            'is_legacy' => true,
            'created_by' => null,
        ];
    }

    public function uploaded(): static
    {
        return $this->state(fn () => [
            'is_legacy' => false,
            'disk' => 'public',
            'path' => '/storage/media/'.fake()->slug(1).'/'.fake()->uuid().'.jpg',
            'storage_path' => 'media/'.fake()->slug(1).'/'.fake()->uuid().'.jpg',
            'created_by' => null,
        ]);
    }
}
