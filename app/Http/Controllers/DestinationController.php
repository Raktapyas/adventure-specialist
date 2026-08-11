<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsFromUrlHistory;
use App\Models\Destination;

class DestinationController extends Controller
{
    use RedirectsFromUrlHistory;

    public function index()
    {
        return view('destinations.index', [
            'destinations' => Destination::published()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->with(['children' => fn ($q) => $q->published()])
                ->get(),
        ]);
    }

    public function show(string $slug)
    {
        $destination = Destination::published()
            ->where('slug', $slug)
            ->with(['children' => fn ($q) => $q->published()])
            ->first();

        if (! $destination) {
            return $this->redirectFromHistory();
        }

        return $this->render($destination);
    }

    public function showByPath()
    {
        $segments = collect(['parent', 'child', 'grandchild'])
            ->map(fn ($key) => request()->route($key))
            ->filter()
            ->values()
            ->all();

        $root = request()->route('root');

        if ($root && ($segments[0] ?? null) !== $root) {
            array_unshift($segments, $root);
        }

        $destination = Destination::resolvePath($segments);

        if (! $destination) {
            return $this->redirectFromHistory();
        }

        return $this->render($destination);
    }

    protected function render(Destination $destination)
    {
        $canonical = $destination->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        $destination->load('parent.parent');
        $destination->parent?->load(['children' => fn ($q) => $q->published()]);

        return view('destinations.show', [
            'destination' => $destination,
            'siblings' => $destination->parent ? $destination->parent->children : collect(),
        ]);
    }
}
