<?php

namespace App\View\Composers;

use App\Models\Destination;
use App\Models\Page;
use App\Models\Service;
use Illuminate\View\View;

class NavComposer
{
    public function compose(View $view): void
    {
        if (request()->is('admin/*') || request()->is('admin')) {
            return;
        }

        $view->with('navAboutPages', Page::whereHas('parent', fn ($q) => $q->where('slug', 'about'))->with('parent')->orderBy('sort_order')->get());

        $view->with('navServices', Service::whereNull('parent_id')->orderBy('sort_order')->with('children.parent')->get());

        $view->with('navDestinations', Destination::whereNull('parent_id')->where('slug', '!=', 'nepal')->orderBy('sort_order')->with('children.parent')->get());

        $view->with('navNepal', Destination::where('slug', 'nepal')->with('children.parent')->first());
    }
}
