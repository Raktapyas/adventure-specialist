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
     * True when the file physically exists for this row.
     */
    public function fileExists(): bool
    {
        if ($this->is_legacy) {
            return is_file(public_path($this->path));
        }

        return $this->storage_path !== null
            && Storage::disk($this->disk)->exists($this->storage_path);
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
}
