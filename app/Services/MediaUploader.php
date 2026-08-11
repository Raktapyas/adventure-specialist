<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MediaUploader
{
    private const MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Validate and persist a single uploaded image.
     *
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function store(UploadedFile $file, ?int $userId, ?string $altText = null): Media
    {
        $extension = $this->assertSafe($file);

        $disk = config('media.disk');
        $storagePath = $file->store(Str::of(config('media.directory'))->append('/')->append(date('Y/m')), $disk);

        if ($storagePath === false) {
            throw new RuntimeException('Failed to store the uploaded file.');
        }

        return Media::create([
            'name' => $this->sanitizeName($file->getClientOriginalName()),
            'path' => Storage::disk($disk)->url($storagePath),
            'disk' => $disk,
            'storage_path' => $storagePath,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
            'alt_text' => $altText,
            'is_legacy' => false,
            'created_by' => $userId,
        ]);
    }

    /**
     * Reject path traversal / control characters and verify that the uploaded
     * bytes really are an allowed image whose extension matches its contents.
     *
     * @throws ValidationException
     */
    protected function assertSafe(UploadedFile $file): string
    {
        $name = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if ($name === '' || str_contains($name, '/') || str_contains($name, '..') || str_contains($name, "\0")) {
            $this->fail('The filename is not allowed.');
        }

        if (! in_array($extension, config('media.allowed_extensions'), true)) {
            $this->fail('The file extension is not allowed.');
        }

        if ($file->getSize() > config('media.max_upload_bytes')) {
            $this->fail('The file must not exceed '.round(config('media.max_upload_bytes') / 1048576).' MB.');
        }

        $path = $file->getRealPath();

        if ($path === false || ! is_file($path)) {
            $this->fail('The uploaded file could not be read.');
        }

        $sniffedMime = (new \finfo(FILEINFO_MIME_TYPE))->buffer(file_get_contents($path));

        if (! in_array($sniffedMime, config('media.allowed_mimes'), true)) {
            $this->fail('The file must be an image ('.implode(', ', config('media.allowed_extensions')).').');
        }

        $size = @getimagesize($path);

        if ($size === false || $size[0] <= 0 || $size[1] <= 0) {
            $this->fail('The file is not a valid image.');
        }

        $expectedExtension = self::MIME_TO_EXTENSION[$sniffedMime] ?? null;

        if ($expectedExtension === null || $extension !== $expectedExtension) {
            $this->fail('The file extension does not match its contents.');
        }

        return $extension;
    }

    /**
     * @throws ValidationException
     */
    protected function fail(string $message): never
    {
        throw ValidationException::withMessages(['media' => [$message]]);
    }

    protected function sanitizeName(string $original): string
    {
        $base = preg_replace('/[\x00-\x1F\x7F]+/u', '', pathinfo($original, PATHINFO_FILENAME));
        $base = trim((string) $base, " \t\n\r\0\x0B.");

        return $base !== '' ? $base : 'image';
    }
}
