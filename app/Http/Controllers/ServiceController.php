<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsFromUrlHistory;
use App\Models\Service;

class ServiceController extends Controller
{
    use RedirectsFromUrlHistory;

    public function index()
    {
        return view('services.index', [
            'services' => Service::published()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->with(['children' => fn ($q) => $q->published()])
                ->get(),
        ]);
    }

    public function show(string $slug)
    {
        $service = Service::published()
            ->where('slug', $slug)
            ->with(['children' => fn ($q) => $q->published()])
            ->first();

        if (! $service) {
            return $this->redirectFromHistory();
        }

        return $this->render($service);
    }

    public function showByPath(string $parent, string $child)
    {
        $service = Service::resolvePath([$parent, $child]);

        if (! $service) {
            return $this->redirectFromHistory();
        }

        return $this->render($service);
    }

    protected function render(Service $service)
    {
        $canonical = $service->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        $service->load('parent.parent');
        $service->parent?->load(['children' => fn ($q) => $q->published()]);

        return view('services.show', [
            'service' => $service,
            'siblings' => $service->parent ? $service->parent->children : collect(),
        ]);
    }
}
