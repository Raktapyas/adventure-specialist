<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterLegacyMedia extends Command
{
    protected $signature = 'media:register-legacy';

    protected $description = 'Register the existing public/assets/images files as read-only legacy media rows';

    public function handle(): int
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

        $this->info("Legacy media registered: {$created} new, {$already} already present.");

        return self::SUCCESS;
    }
}
