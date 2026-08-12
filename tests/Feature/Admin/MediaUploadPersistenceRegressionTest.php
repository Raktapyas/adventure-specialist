<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class MediaUploadPersistenceRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function validPng(string $name = 'image.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_storage_failure_never_creates_a_media_row(): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

        try {
            app(MediaUploader::class)->store($this->validPng(), null);
            $this->fail('Expected a RuntimeException for the failed store.');
        } catch (RuntimeException $e) {
            $this->assertSame('Failed to store the uploaded file.', $e->getMessage());
        }

        $this->assertDatabaseCount('media', 0);
    }

    public function test_storage_exception_never_creates_a_media_row(): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFileAs')->once()->andThrow(new RuntimeException('disk full'));
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);

        try {
            app(MediaUploader::class)->store($this->validPng(), null);
            $this->fail('Expected a storage exception to bubble up.');
        } catch (RuntimeException $e) {
            $this->assertSame('disk full', $e->getMessage());
        }

        $this->assertDatabaseCount('media', 0);
    }

    public function test_db_insert_failure_removes_the_stored_file(): void
    {
        Storage::fake('public');

        // Sabotage the Media insert so only the disk write happens.
        Media::saving(function () {
            throw new RuntimeException('insert failed');
        });

        try {
            app(MediaUploader::class)->store($this->validPng(), null);
            $this->fail('Expected the sabotaged insert to fail.');
        } catch (RuntimeException $e) {
            $this->assertSame('insert failed', $e->getMessage());
        } finally {
            Media::flushEventListeners();
        }

        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('media'));
    }

    public function test_file_exists_legacy_path_with_traversal_returns_false_safely(): void
    {
        $media = Media::factory()->create(['path' => '/assets/../../../etc/passwd', 'is_legacy' => true]);

        $this->assertFalse($media->fileExists());
    }

    public function test_file_exists_legacy_absolute_path_returns_false_safely(): void
    {
        $media = Media::factory()->create(['path' => 'http://localhost/assets/images/x.jpg', 'is_legacy' => true]);

        $this->assertFalse($media->fileExists());
    }

    public function test_file_exists_legacy_missing_file_returns_false(): void
    {
        $media = Media::factory()->create(['path' => '/assets/images/does-not-exist.jpg', 'is_legacy' => true]);

        $this->assertFalse($media->fileExists());
    }

    public function test_file_exists_legacy_present_file_returns_true(): void
    {
        $file = public_path('assets/images/legacy-exists-check.jpg');
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, 'x');

        try {
            $media = Media::factory()->create(['path' => '/assets/images/legacy-exists-check.jpg', 'is_legacy' => true]);

            $this->assertTrue($media->fileExists());
        } finally {
            @unlink($file);
        }
    }

    public function test_file_exists_uploaded_row_without_disk_or_path_returns_false(): void
    {
        $media = Media::factory()->create([
            'is_legacy' => false,
            'disk' => null,
            'storage_path' => null,
        ]);

        $this->assertFalse($media->fileExists());
    }

    public function test_file_exists_uploaded_unsafe_storage_path_returns_false(): void
    {
        $media = Media::factory()->create([
            'is_legacy' => false,
            'disk' => 'public',
            'storage_path' => '/etc/passwd',
        ]);

        $this->assertFalse($media->fileExists());
    }

    public function test_file_exists_uploaded_present_file_returns_true(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/test/pic.jpg', 'x');

        $media = Media::factory()->create([
            'is_legacy' => false,
            'disk' => 'public',
            'storage_path' => 'media/test/pic.jpg',
        ]);

        $this->assertTrue($media->fileExists());
    }
}
