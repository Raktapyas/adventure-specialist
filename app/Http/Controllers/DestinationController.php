<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class DestinationController extends Controller
{
    public function index()
    {
        return view('destinations.index', [
            'destinations' => Destination::whereNull('parent_id')->orderBy('sort_order')->with('children.parent')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $destination = Destination::where('slug', $slug)->with('children.parent')->firstOrFail();

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

        abort_unless($destination, 404);

        return $this->render($destination);
    }

    protected function render(Destination $destination)
    {
        $canonical = $destination->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        $destination->load('parent.parent');

        return view('destinations.show', [
            'destination' => $destination,
            'siblings' => $destination->parent ? $destination->parent->children : collect(),
        ]);
    }
}
