<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize absolute storage URLs (e.g. "http://localhost/storage/...")
     * stored in media paths and model image fields to host-relative paths
     * (e.g. "/storage/..."). Legacy "/assets/images/..." paths are untouched.
     *
     * The migration is idempotent: rows that are already host-relative (or
     * null) are left alone, so re-running it is a no-op.
     */
    public function up(): void
    {
        $this->normalizeColumn('media', 'path');
        $this->normalizeColumn('pages', 'cover_image');
        $this->normalizeColumn('services', 'cover_image');
        $this->normalizeColumn('destinations', 'cover_image');
        $this->normalizeColumn('packages', 'cover_image');
        $this->normalizeColumn('gallery_images', 'image_url');
    }

    /**
     * Best-effort reversal: re-prefix host-relative "/storage/..." paths with
     * the configured app URL. Legacy "/assets/images/..." paths are untouched.
     */
    public function down(): void
    {
        $this->restoreColumn('media', 'path');
        $this->restoreColumn('pages', 'cover_image');
        $this->restoreColumn('services', 'cover_image');
        $this->restoreColumn('destinations', 'cover_image');
        $this->restoreColumn('packages', 'cover_image');
        $this->restoreColumn('gallery_images', 'image_url');
    }

    private function normalizeColumn(string $table, string $column): void
    {
        $rows = DB::table($table)
            ->where($column, 'like', 'http://%')
            ->orWhere($column, 'like', 'https://%')
            ->get(['id', $column]);

        foreach ($rows as $row) {
            $relative = $this->toHostRelative((string) $row->{$column});

            if ($relative !== $row->{$column}) {
                DB::table($table)->where('id', $row->id)->update([$column => $relative]);
            }
        }
    }

    private function toHostRelative(string $value): string
    {
        $parts = parse_url($value);

        if (! isset($parts['scheme']) || ! in_array($parts['scheme'], ['http', 'https'], true)) {
            return $value;
        }

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function restoreColumn(string $table, string $column): void
    {
        $base = rtrim((string) config('app.url'), '/');

        $rows = DB::table($table)
            ->where($column, 'like', '/storage/%')
            ->get(['id', $column]);

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update([$column => $base.$row->{$column}]);
        }
    }
};
