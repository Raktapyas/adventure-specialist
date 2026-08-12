<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'disk',
        'storage_path',
        'mime_type',
        'extension',
        'size',
        'alt_text',
        'is_legacy',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'is_legacy' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    /**
     * The public URL of the file. Legacy media already live in the web root,
     * so their stored path is served directly.
     */
    public function url(): string
    {
        return $this->path;
    }

    /**
     * Canonical host-relative form of a stored image reference.
     *
     * Absolute http(s) URLs (e.g. pasted browser addresses or legacy data)
     * are reduced to their path (plus any query), so they match the
     * host-relative path convention used by uploads and legacy media. Any
     * other value is returned unchanged.
     */
    public static function normalizePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $value = trim($path);
        $parts = parse_url($value);

        if (isset($parts['scheme']) && in_array($parts['scheme'], ['http', 'https'], true)) {
            $value = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        }

        return $value;
    }

    /**
     * True when the file physically exists for this row.
     *
     * The check is defensive: malformed rows (missing disk, tampered paths
     * with traversal or absolute filesystem paths) report "missing" instead of
     * probing arbitrary paths or throwing. Delete protection is unaffected —
     * it is driven by usage rows, never by file existence.
     */
    public function fileExists(): bool
    {
        if ($this->is_legacy) {
            if (! $this->isSafeWebPath($this->path)) {
                return false;
            }

            return is_file(public_path($this->path));
        }

        if ($this->storage_path === null || $this->disk === null || $this->disk === '') {
            return false;
        }

        if (! $this->isSafeStoragePath($this->storage_path)) {
            return false;
        }

        try {
            return Storage::disk($this->disk)->exists($this->storage_path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return number_format($bytes, $bytes < 10 && $unit !== 'B' ? 1 : 0).' '.$unit;
            }

            $bytes /= 1024;
        }

        return number_format($bytes, 1).' TB';
    }

    public function usageCount(): int
    {
        return $this->usages()->count();
    }

    /**
     * Every usage described as "Model#id · field" for delete-guard messaging.
     *
     * @return list<string>
     */
    public function usageLabels(): array
    {
        return $this->usages->map(fn (MediaUsage $usage) => trim(
            str_replace('App\\Models\\', '', $usage->model_type).'#'.$usage->model_id.' · '.$usage->field
        ))->all();
    }

    /**
     * A host-relative web path is safe when it starts with a single slash,
     * has no scheme, no traversal, and no filesystem separators.
     */
    protected function isSafeWebPath(string $path): bool
    {
        return str_starts_with($path, '/')
            && ! str_starts_with($path, '//')
            && ! str_contains($path, '..')
            && ! str_contains($path, '\\')
            && ! str_contains($path, ':');
    }

    /**
     * A stored (disk-relative) path is safe when it is relative, without
     * traversal, filesystem separators, or a drive/colon prefix.
     */
    protected function isSafeStoragePath(string $path): bool
    {
        return ! str_starts_with($path, '/')
            && ! str_contains($path, '..')
            && ! str_contains($path, '\\')
            && ! str_contains($path, ':');
    }
}
