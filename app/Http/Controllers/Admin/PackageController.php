<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Models\Package;
use App\Services\MediaUsageService;
use App\Services\UrlHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.packages.index', [
            'packages' => Package::orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackageRequest $request): RedirectResponse
    {
        $package = Package::create($request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($package, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($package, $request->validated('content'));

        return redirect()->route('admin.packages.index')
            ->with('status', 'Package created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package): View
    {
        return view('admin.packages.edit', [
            'package' => $package,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        app(UrlHistoryService::class)->update($package, $request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($package, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($package, $request->validated('content'));

        return redirect()->route('admin.packages.edit', $package)
            ->with('status', 'Package updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();
        app(UrlHistoryService::class)->purge($package);
        app(MediaUsageService::class)->purgeModel($package);

        return redirect()->route('admin.packages.index')
            ->with('status', 'Package deleted.');
    }
}
