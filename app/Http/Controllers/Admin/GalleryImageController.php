<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryImageRequest;
use App\Http\Requests\Admin\UpdateGalleryImageRequest;
use App\Models\GalleryImage;
use App\Services\MediaUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalleryImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.gallery.index', [
            'images' => GalleryImage::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.gallery.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGalleryImageRequest $request): RedirectResponse
    {
        $gallery = GalleryImage::create($request->validated());

        app(MediaUsageService::class)->sync($gallery, 'image_url', $request->validated('image_url'));

        return redirect()->route('admin.gallery.index')
            ->with('status', 'Image added.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GalleryImage $gallery): View
    {
        return view('admin.gallery.edit', [
            'image' => $gallery,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGalleryImageRequest $request, GalleryImage $gallery): RedirectResponse
    {
        $gallery->update($request->validated());

        app(MediaUsageService::class)->sync($gallery, 'image_url', $request->validated('image_url'));

        return redirect()->route('admin.gallery.edit', $gallery)
            ->with('status', 'Image updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GalleryImage $gallery): RedirectResponse
    {
        $gallery->delete();
        app(MediaUsageService::class)->purgeModel($gallery);

        return redirect()->route('admin.gallery.index')
            ->with('status', 'Image removed.');
    }
}
