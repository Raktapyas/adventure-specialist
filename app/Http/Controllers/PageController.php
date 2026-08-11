<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsFromUrlHistory;
use App\Models\Page;

class PageController extends Controller
{
    use RedirectsFromUrlHistory;

    public function show(string $slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->with([
                'children' => fn ($q) => $q->published(),
                'parent.children' => fn ($q) => $q->published(),
            ])
            ->first();

        if (! $page) {
            return $this->redirectFromHistory();
        }

        $canonical = $page->getPath();

        if (request()->getPathInfo() !== $canonical) {
            return redirect()->away($canonical, 301);
        }

        return view('pages.show', [
            'page' => $page,
            'siblings' => $page->parent ? $page->parent->children : collect(),
        ]);
    }

    public function managingDirector()
    {
        $page = Page::published()->where('slug', 'managing-director')->first();

        if (! $page) {
            return $this->redirectFromHistory();
        }

        return view('pages.managing-director', ['page' => $page]);
    }
}
