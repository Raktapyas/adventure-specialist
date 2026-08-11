<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\UrlHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::with('children')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.pages.create', [
            'pages' => Page::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePageRequest $request): RedirectResponse
    {
        Page::create($request->validated());

        return redirect()->route('admin.pages.index')
            ->with('status', 'Page created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'pages' => Page::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        app(UrlHistoryService::class)->update($page, $request->validated());

        return redirect()->route('admin.pages.edit', $page)
            ->with('status', 'Page updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        if ($page->children()->exists()) {
            return redirect()->route('admin.pages.index')
                ->with('error', 'Cannot delete a page that has children.');
        }

        $page->delete();
        app(UrlHistoryService::class)->purge($page);

        return redirect()->route('admin.pages.index')
            ->with('status', 'Page deleted.');
    }
}
