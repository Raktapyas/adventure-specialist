<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaUploader $uploader,
    ) {}

    /**
     * Display the media library.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = $request->query('type');
        $source = $request->query('source');

        $media = Media::with('uploader')->withCount('usages')
            ->search($search)
            ->when(in_array($type, config('media.allowed_extensions'), true), fn ($q) => $q->where('extension', $type))
            ->when($source === 'uploaded', fn ($q) => $q->where('is_legacy', false))
            ->when($source === 'legacy', fn ($q) => $q->where('is_legacy', true))
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'source' => $source,
            ],
        ]);
    }

    /**
     * Show the upload form.
     */
    public function create(): View
    {
        return view('admin.media.create');
    }

    /**
     * Store uploaded images.
     */
    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $stored = 0;

        foreach ($request->file('media', []) as $file) {
            $this->uploader->store($file, $request->user()?->id, $request->validated('alt_text'));
            $stored++;
        }

        return redirect()->route('admin.media.index')
            ->with('status', "{$stored} image".($stored === 1 ? '' : 's').' uploaded.');
    }

    /**
     * JSON list backing the picker modal.
     */
    public function pickerData(Request $request): JsonResponse
    {
        $media = Media::withCount('usages')
            ->search($request->query('search'))
            ->orderByDesc('id')
            ->paginate(48);

        return response()->json([
            'items' => $media->map(fn (Media $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'url' => $m->url(),
                'size' => $m->humanSize(),
                'extension' => $m->extension,
                'is_legacy' => $m->is_legacy,
                'usages' => $m->usages_count,
            ]),
            'has_more' => $media->hasMorePages(),
            'next_page' => $media->currentPage() + 1,
        ]);
    }

    /**
     * Remove a media row. Uploaded files are deleted from disk; legacy rows
     * are unregistered only (their physical web-root file is never touched).
     * Rows that are still referenced are protected unless force is passed.
     */
    public function destroy(Request $request, Media $medium): RedirectResponse
    {
        $isLegacy = $medium->is_legacy;

        if (! $request->boolean('force')) {
            $medium->load('usages');

            if ($medium->usages->isNotEmpty()) {
                $labels = $medium->usageLabels();

                $message = 'This image is in use by '.$medium->usages->count().' item(s)'
                    .($labels !== [] ? ': '.implode(', ', $labels) : '')
                    .'. Reassign or remove those references first, or force-delete.';

                return redirect()->route('admin.media.index')->with('error', $message);
            }
        }

        DB::transaction(function () use ($medium) {
            if (! $medium->is_legacy && $medium->storage_path !== null) {
                Storage::disk($medium->disk)->delete($medium->storage_path);
            }

            $medium->delete();
        });

        $message = $isLegacy
            ? 'Legacy image unregistered (the original file was kept in place).'
            : 'Image deleted.';

        return redirect()->route('admin.media.index')->with('status', $message);
    }
}
