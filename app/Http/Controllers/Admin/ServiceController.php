<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Services\MediaUsageService;
use App\Services\UrlHistoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::with('children')->orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.services.create', [
            'services' => Service::orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::create($request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($service, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($service, $request->validated('content'));

        return redirect()->route('admin.services.index')
            ->with('status', 'Service created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'services' => $this->selectableParents($service),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        app(UrlHistoryService::class)->update($service, $request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($service, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($service, $request->validated('content'));

        return redirect()->route('admin.services.edit', $service)
            ->with('status', 'Service updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        if ($service->children()->exists()) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Cannot delete a service that has children.');
        }

        $service->delete();
        app(UrlHistoryService::class)->purge($service);
        app(MediaUsageService::class)->purgeModel($service);

        return redirect()->route('admin.services.index')
            ->with('status', 'Service deleted.');
    }

    protected function selectableParents(Service $service): Collection
    {
        $excluded = array_merge([$service->id], $service->descendantIds());

        return Service::whereNotIn('id', $excluded)->orderBy('sort_order')->orderBy('title')->get();
    }
}
