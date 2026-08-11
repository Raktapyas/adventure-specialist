<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationRequest;
use App\Models\Destination;
use App\Services\MediaUsageService;
use App\Services\UrlHistoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.destinations.index', [
            'destinations' => Destination::with('children')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.destinations.create', [
            'destinations' => Destination::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDestinationRequest $request): RedirectResponse
    {
        $destination = Destination::create($request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($destination, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($destination, $request->validated('content'));

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destination created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destination $destination): View
    {
        return view('admin.destinations.edit', [
            'destination' => $destination,
            'destinations' => $this->selectableParents($destination),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDestinationRequest $request, Destination $destination): RedirectResponse
    {
        app(UrlHistoryService::class)->update($destination, $request->validated());

        $usage = app(MediaUsageService::class);
        $usage->sync($destination, 'cover_image', $request->validated('cover_image'));
        $usage->syncContent($destination, $request->validated('content'));

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('status', 'Destination updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Destination $destination): RedirectResponse
    {
        if ($destination->children()->exists()) {
            return redirect()->route('admin.destinations.index')
                ->with('error', 'Cannot delete a destination that has children.');
        }

        $destination->delete();
        app(UrlHistoryService::class)->purge($destination);
        app(MediaUsageService::class)->purgeModel($destination);

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destination deleted.');
    }

    protected function selectableParents(Destination $destination): Collection
    {
        $excluded = array_merge([$destination->id], $destination->descendantIds());

        return Destination::whereNotIn('id', $excluded)->orderBy('sort_order')->get();
    }
}
