<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/services.json')), true);

        foreach ($rows as $row) {
            $parent = $row['parent_slug'] ? Service::where('slug', $row['parent_slug'])->first() : null;

            Service::firstOrCreate(
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
