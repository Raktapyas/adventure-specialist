<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SyncLegacyMediaSeeder extends Seeder
{
    /**
     * Scan public/assets/images and register any file that is not already in
     * the media table as a read-only legacy Media row. Idempotent: files whose
     * host-relative path already exists are skipped, so re-running is safe.
     */
    public function run(): void
    {
        $root = public_path('assets/images');
        $created = 0;
        $already = 0;

        foreach (File::allFiles($root) as $file) {
            if (! in_array(strtolower($file->getExtension()), config('media.allowed_extensions'), true)) {
                continue;
            }

            $path = '/assets/images/'.str_replace('\\', '/', ltrim($file->getRelativePathname(), '/'));

            if (Media::where('path', $path)->exists()) {
                $already++;

                continue;
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer(File::get($file->getPathname()));

            Media::create([
                'name' => $file->getFilename(),
                'path' => $path,
                'disk' => null,
                'storage_path' => null,
                'mime_type' => $mime,
                'extension' => strtolower($file->getExtension()),
                'size' => $file->getSize(),
                'alt_text' => null,
                'is_legacy' => true,
                'created_by' => null,
            ]);

            $created++;
        }

        $this->command?->info("Legacy media synced: {$created} new, {$already} already present.");
    }
}
