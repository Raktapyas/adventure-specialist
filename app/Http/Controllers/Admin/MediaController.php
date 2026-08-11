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
use Illuminate\Validation\ValidationException;
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
        $query = Media::with('uploader')->withCount('usages');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            });
        }

        if ($request->has('type') && in_array($request->query('type'), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $query->where('extension', $request->query('type'));
        }

        if ($request->query('source') === 'uploaded') {
            $query->where('is_legacy', false);
        } elseif ($request->query('source') === 'legacy') {
            $query->where('is_legacy', true);
        }

        $media = $query->orderByDesc('id')->paginate(24)->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'filters' => [
                'search' => $search,
                'type' => $request->query('type'),
                'source' => $request->query('source'),
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
            try {
                $this->uploader->store($file, $request->user()?->id, $request->validated('alt_text'));
                $stored++;
            } catch (ValidationException $e) {
                throw $e;
            }
        }

        return redirect()->route('admin.media.index')
            ->with('status', "{$stored} image".($stored === 1 ? '' : 's').' uploaded.');
    }

    /**
     * JSON list backing the picker modal.
     */
    public function pickerData(Request $request): JsonResponse
    {
        $query = Media::withCount('usages');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('path', 'like', "%{$search}%");
            });
        }

        $media = $query->orderByDesc('id')->paginate(48);

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
        $usageCount = $medium->usages()->count();
        $isLegacy = $medium->is_legacy;

        if ($usageCount > 0 && ! $request->boolean('force')) {
            return redirect()->route('admin.media.index')
                ->with('error', 'This image is in use by '.$usageCount.' item(s). Reassign or remove those references first, or force-delete.');
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
