<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MediaUploader
{
    /**
     * Extensions the server accepts for each sniffed MIME type. JPEG may use
     * either ".jpg" or ".jpeg"; everything else has a single canonical
     * extension. The stored extension is the client-declared one, so ".jpeg"
     * uploads keep their ".jpeg" suffix while still matching the sniffed
     * "image/jpeg" contents.
     */
    private const MIME_EXTENSIONS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'image/gif' => ['gif'],
    ];

    private static ?\finfo $finfo = null;

    /**
     * Validate and persist a single uploaded image.
     *
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function store(UploadedFile $file, ?int $userId, ?string $altText = null): Media
    {
        $safe = $this->assertSafe($file);

        $disk = config('media.disk');
        $storagePath = $file->store(config('media.directory').'/'.date('Y/m'), $disk);

        if ($storagePath === false) {
            throw new RuntimeException('Failed to store the uploaded file.');
        }

        try {
            return Media::create([
                'name' => $this->sanitizeName($file->getClientOriginalName()),
                'path' => $this->hostRelativeUrl($disk, $storagePath),
                'disk' => $disk,
                'storage_path' => $storagePath,
                'mime_type' => $safe['mime_type'],
                'extension' => $safe['extension'],
                'size' => $file->getSize(),
                'alt_text' => $altText,
                'is_legacy' => false,
                'created_by' => $userId,
            ]);
        } catch (\Throwable $e) {
            Storage::disk($disk)->delete($storagePath);

            throw $e;
        }
    }

    /**
     * The web path of a stored file, relative to the app host (e.g.
     * "/storage/media/2026/08/..."). Storing a host-relative path keeps
     * thumbnails working regardless of the host/port the browser uses,
     * matching the convention used by legacy media and the media factory.
     */
    protected function hostRelativeUrl(string $disk, string $storagePath): string
    {
        $path = parse_url(Storage::disk($disk)->url($storagePath), PHP_URL_PATH);

        return $path !== false && $path !== null ? $path : '/'.$storagePath;
    }

    /**
     * Reject path traversal / control characters and verify that the uploaded
     * bytes really are an allowed image whose extension matches its contents.
     *
     * @return array{extension: string, mime_type: string}
     *
     * @throws ValidationException
     */
    protected function assertSafe(UploadedFile $file): array
    {
        $name = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = config('media.allowed_extensions');
        $allowedMimes = config('media.allowed_mimes');
        $maxBytes = config('media.max_upload_bytes');

        if ($name === '' || str_contains($name, '/') || str_contains($name, '..') || str_contains($name, "\0")) {
            $this->fail('The filename is not allowed.');
        }

        if (! in_array($extension, $allowedExtensions, true)) {
            $this->fail('The file extension is not allowed.');
        }

        if ($file->getSize() > $maxBytes) {
            $this->fail('The file must not exceed '.round($maxBytes / 1048576).' MB.');
        }

        $path = $file->getRealPath();

        if ($path === false || ! is_file($path)) {
            $this->fail('The uploaded file could not be read.');
        }

        $sniffedMime = (self::$finfo ??= new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! in_array($sniffedMime, $allowedMimes, true)) {
            $this->fail('The file must be an image ('.implode(', ', $allowedExtensions).').');
        }

        $size = @getimagesize($path);

        if ($size === false || $size[0] <= 0 || $size[1] <= 0) {
            $this->fail('The file is not a valid image.');
        }

        $mimeExtensions = self::MIME_EXTENSIONS[$sniffedMime] ?? null;

        if ($mimeExtensions === null || ! in_array($extension, $mimeExtensions, true)) {
            $this->fail('The file extension does not match its contents.');
        }

        return ['extension' => $extension, 'mime_type' => $sniffedMime];
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
