<?php

namespace App\View\Composers;

use App\Models\Destination;
use App\Models\Page;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NavComposer
{
    /**
     * The view names that render the primary navigation / footer. Registering
     * only here (instead of "*") stops the nav queries from firing once per
     * Blade component on every page.
     */
    public const VIEWS = [
        'components.navbar',
        'components.footer',
        'pages.show',
        'services.show',
        'destinations.show',
    ];

    private ?array $cache = null;

    public function compose(View $view): void
    {
        if (request()->is('admin/*') || request()->is('admin')) {
            return;
        }

        if ($this->cache === null) {
            $this->cache = $this->load();
        }

        $view->with('navAboutPages', $this->cache['aboutPages']);
        $view->with('navTopLevelPages', $this->cache['topLevelPages']);
        $view->with('navServices', $this->cache['services']);
        $view->with('navDestinations', $this->cache['destinations']);
        $view->with('navNepal', $this->cache['nepal']);
        $view->with('siteContact', $this->cache['siteContact']);
        $view->with('siteBranding', $this->cache['branding']);
    }

    /**
     * @return array{aboutPages: Collection, topLevelPages: Collection, services: Collection, destinations: Collection, nepal: ?Destination, siteContact: array<string, ?string>, branding: array{logo: string, logo_white: string}}
     */
    protected function load(): array
    {
        $aboutPages = Page::published()
            ->whereHas('parent', fn ($q) => $q->where('slug', 'about'))
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $topLevelPages = Page::published()
            ->whereNull('parent_id')
            ->whereNotIn('slug', ['about', 'managing-director'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')])])
            ->get();
        // Hydrate child->parent to prevent N+1 in getPath()/slugChain() (1st + 2nd level)
        $topLevelPages->each(function (Page $page): void {
            $page->children->each(function (Page $child) use ($page): void {
                $child->setRelation('parent', $page);
                $child->children->each->setRelation('parent', $child);
            });
        });

        $services = Service::published()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')])])
            ->get();
        $services->each(function (Service $service): void {
            $service->children->each(function (Service $child) use ($service): void {
                $child->setRelation('parent', $service);
                $child->children->each->setRelation('parent', $child);
            });
        });

        $destinations = Destination::published()
            ->whereNull('parent_id')
            ->where('slug', '!=', 'nepal')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')])])
            ->get();
        $destinations->each(function (Destination $destination): void {
            $destination->children->each(function (Destination $child) use ($destination): void {
                $child->setRelation('parent', $destination);
                $child->children->each->setRelation('parent', $child);
            });
        });

        $nepal = Destination::published()
            ->where('slug', 'nepal')
            ->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')])])
            ->first();
        if ($nepal) {
            $nepal->children->each(function (Destination $child) use ($nepal): void {
                $child->setRelation('parent', $nepal);
                $child->children->each->setRelation('parent', $child);
            });
        }

        return [
            'aboutPages' => $aboutPages,
            'topLevelPages' => $topLevelPages,
            'services' => $services,
            'destinations' => $destinations,
            'nepal' => $nepal,
            'siteContact' => SiteSetting::current()->contactBlock(),
            'branding' => SiteSetting::current()->brandingBlock(),
        ];
    }
}
