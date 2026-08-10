<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/pages.json')), true);

        foreach ($rows as $row) {
            $parent = $row['parent_slug'] ? Page::where('slug', $row['parent_slug'])->first() : null;

            Page::updateOrCreate(
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
