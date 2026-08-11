<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/packages.json')), true);

        foreach ($rows as $row) {
            Package::firstOrCreate(
                ['slug' => $row['slug']],
                [
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
