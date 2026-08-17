<?php

namespace Tests\Feature;

use App\Models\Media;
use Database\Seeders\SyncLegacyMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncLegacyMediaSeederTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_it_registers_missing_legacy_files(): void
    {
        $file = public_path('assets/images/seeder-test.png');
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, base64_decode(self::PNG));

        try {
            $this->seed(SyncLegacyMediaSeeder::class);

            $media = Media::where('path', '/assets/images/seeder-test.png')->first();

            $this->assertNotNull($media);
            $this->assertSame('seeder-test.png', $media->name);
            $this->assertSame('/assets/images/seeder-test.png', $media->path);
            $this->assertNull($media->disk);
            $this->assertNull($media->storage_path);
            $this->assertSame('image/png', $media->mime_type);
            $this->assertSame('png', $media->extension);
            $this->assertSame(strlen(base64_decode(self::PNG)), $media->size);
            $this->assertNull($media->alt_text);
            $this->assertTrue($media->is_legacy);
            $this->assertNull($media->created_by);
        } finally {
            @unlink($file);
        }
    }

    public function test_it_is_idempotent(): void
    {
        $file = public_path('assets/images/seeder-test.png');
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, base64_decode(self::PNG));

        try {
            $this->seed(SyncLegacyMediaSeeder::class);
            $this->seed(SyncLegacyMediaSeeder::class);

            $this->assertSame(1, Media::where('path', '/assets/images/seeder-test.png')->count());
        } finally {
            @unlink($file);
        }
    }
}
