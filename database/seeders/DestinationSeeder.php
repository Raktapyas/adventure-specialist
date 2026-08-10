<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/destinations.json')), true);

        foreach ($rows as $row) {
            $parent = $row['parent_slug'] ? Destination::where('slug', $row['parent_slug'])->first() : null;

            Destination::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'parent_id' => $parent?->id,
                    'title' => $row['title'],
                    'excerpt' => $row['excerpt'],
                    'content' => $row['content'],
                    'cover_image' => $row['cover_image'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
