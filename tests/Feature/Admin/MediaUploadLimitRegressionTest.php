<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Tests\TestCase;

class MediaUploadLimitRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
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
     * UPLOAD_ERR_INI_SIZE before Laravel validation runs. In a browser the
     * FileUpload field surfaces "The media failed to upload."; the contract we
     * can assert in the test harness is that such a file never creates a record.
     *
     * This is the exact failure mode behind the reported regression.
     */
    public function test_upload_rejected_by_php_ini_ini_size_never_creates_a_record(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tmp, str_repeat('x', 3 * 1024 * 1024)); // 3 MB

        $file = new SymfonyUploadedFile($tmp, 'big.png', 'image/png', UPLOAD_ERR_INI_SIZE, true);

        try {
            Livewire::actingAs($this->admin())
                ->test(ListMedia::class)
                ->callAction('upload', data: ['media' => [$file]]);
        } catch (CorruptComponentPayloadException) {
            // Livewire's test harness cannot hydrate a component after a file
            // rejected by PHP's upload_max_filesize. The important contract is
            // that no record is ever created.
        }

        $this->assertDatabaseCount('media', 0);
    }

    /**
     * The app's declared upload contracts must stay at 5 MB for images and
     * 50 MB for videos, and the documented server requirement must not be
     * lowered below them. This guards against "fixing" the INI-limit
     * regression by weakening application validation.
     */
    public function test_application_upload_limit_contract_is_preserved(): void
    {
        $this->assertSame(5 * 1024 * 1024, config('media.max_image_bytes'));
        $this->assertSame(50 * 1024 * 1024, config('media.max_video_bytes'));

        // A VALID image past the cap must reach the size check (not be
        // rejected earlier by the MIME sniff) and report the 5 MB contract.
        $file = UploadedFile::fake()->createWithContent('big.png', $this->paddedPng(6 * 1024 * 1024));

        try {
            app(MediaUploader::class)->store($file, null);
            $this->fail('Expected ValidationException for an oversized file.');
        } catch (ValidationException $e) {
            $this->assertSame(['The file must not exceed 5 MB.'], $e->errors()['media']);
        }
    }

    public function test_oversized_video_is_rejected_at_the_video_cap(): void
    {
        config()->set('media.max_video_bytes', 2 * 1024 * 1024);

        $mp4 = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom"."\x00\x00\x00\x08free";
        $file = UploadedFile::fake()->createWithContent('clip.mp4', $mp4.str_repeat('x', 3 * 1024 * 1024));

        try {
            app(MediaUploader::class)->store($file, null);
            $this->fail('Expected ValidationException for an oversized video.');
        } catch (ValidationException $e) {
            $this->assertSame(['The file must not exceed 2 MB.'], $e->errors()['media']);
        }
    }

    /**
     * A large valid PNG (3 MB, within the app's 5 MB limit) uploads end-to-end
     * when the server allows it: database row, stored file, and a served
     * public URL.
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

        Livewire::actingAs($admin)
            ->test(ListMedia::class)
            ->callAction('upload', data: ['media' => [$file]]);

        $this->assertDatabaseCount('media', 1);
        $media = Media::firstOrFail();
        $this->assertFalse($media->is_legacy);
        $this->assertSame('png', $media->extension);
        $this->assertGreaterThan(2 * 1024 * 1024, $media->size);

        Storage::disk('public')->assertExists($media->storage_path);
    }
}
