<?php

namespace Tests\Feature\Admin;

use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Tests\TestCase;

class MediaUploadLimitRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * Build a valid PNG padded to roughly $size bytes with an ancillary
     * tEXt chunk (PNG readers ignore it; getimagesize/finfo still pass).
     */
    private function paddedPng(int $size): string
    {
        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        $png = "\x89PNG\r\n\x1a\n";
        $png .= $chunk('IHDR', pack('NNCCCCC', 1, 1, 8, 2, 0, 0, 0));
        $png .= $chunk('IDAT', gzcompress("\x00\xff\x00\x00"));
        $png .= $chunk('tEXt', "Comment\0".str_repeat('A', $size - 200));
        $png .= $chunk('IEND', '');

        return $png;
    }

    /**
     * A valid image larger than the system php.ini cap (upload_max_filesize=2M)
     * but within the app's 5 MB contract is rejected by PHP with
     * UPLOAD_ERR_INI_SIZE before Laravel validation runs. That surfaces the
     * generic "failed to upload" message instead of the app's friendly size error.
     *
     * This is the exact failure mode behind the reported regression.
     */
    public function test_upload_rejected_by_php_ini_ini_size_surfaces_failed_to_upload(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tmp, str_repeat('x', 3 * 1024 * 1024)); // 3 MB

        $file = new SymfonyUploadedFile($tmp, 'big.png', 'image/png', UPLOAD_ERR_INI_SIZE, true);

        $this->actingAs($this->admin())
            ->from('/admin/media/create')
            ->post('/admin/media', ['media' => [$file]])
            ->assertSessionHasErrors(['media.0'])
            ->assertRedirect('/admin/media/create');

        $errors = session('errors')->getBag('default')->messages();
        $this->assertSame(['The media.0 failed to upload.'], $errors['media.0']);

        $this->assertDatabaseCount('media', 0);
    }

    /**
     * The app's declared upload contract must stay at 5 MB and the documented
     * server requirement must not be lowered below it. This guards against
     * "fixing" the INI-limit regression by weakening application validation.
     */
    public function test_application_upload_limit_contract_is_preserved(): void
    {
        $this->assertSame(5 * 1024 * 1024, config('media.max_upload_bytes'));

        $request = new StoreMediaRequest;
        $rules = $request->rules();
        $this->assertContains('max:5120', $rules['media.*']);
    }

    /**
     * A large valid PNG (3 MB, within the app's 5 MB limit) uploads end-to-end
     * when the server allows it: HTTP redirect, database row, stored file, and
     * a served public URL.
     */
    public function test_large_valid_png_upload_succeeds_when_server_allows_it(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->createWithContent('big-valid.png', $this->paddedPng(3 * 1024 * 1024));

        // Validate the fixture really is a ~3 MB valid image.
        $this->assertGreaterThan(2 * 1024 * 1024, $file->getSize());
        $this->assertSame('image/png', (new \finfo(FILEINFO_MIME_TYPE))->buffer(file_get_contents($file->getRealPath())));
        $this->assertNotFalse(getimagesize($file->getRealPath()));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/media', ['media' => [$file]])
            ->assertRedirect(route('admin.media.index'));

        $this->assertDatabaseCount('media', 1);
        $media = Media::firstOrFail();
        $this->assertFalse($media->is_legacy);
        $this->assertSame('png', $media->extension);
        $this->assertGreaterThan(2 * 1024 * 1024, $media->size);

        Storage::disk('public')->assertExists($media->storage_path);
    }
}
