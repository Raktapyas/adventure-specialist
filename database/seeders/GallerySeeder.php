<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(File::get(database_path('data/gallery.json')), true);

        foreach ($rows as $row) {
            GalleryImage::updateOrCreate(
                ['caption' => $row['caption']],
                [
                    'image_url' => $row['image_url'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
