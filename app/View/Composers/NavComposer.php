<?php

namespace App\View\Composers;

use App\Models\Destination;
use App\Models\NavigationItem;
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
        $view->with('navItems', $this->cache['navItems']);
        $view->with('siteContact', $this->cache['siteContact']);
        $view->with('siteBranding', $this->cache['branding']);
        $view->with('footerHeadings', $this->cache['footerHeadings']);
        $view->with('footerServices', $this->cache['footerServices']);
        $view->with('footerDestinations', $this->cache['footerDestinations']);
        $view->with('navFlyoutStyle', $this->cache['navFlyoutStyle']);
    }

    /**
     * @return array{aboutPages: Collection, topLevelPages: Collection, services: Collection, destinations: Collection, nepal: ?Destination, navItems: Collection, siteContact: array<string, ?string>, branding: array{logo: string, logo_white: string}, footerHeadings: array{services: string, destinations: string, contact: string}, footerServices: Collection, footerDestinations: Collection, navFlyoutStyle: string}
     */
    protected function load(): array
    {
        $aboutPages = Page::published()
            ->whereHas('parent', fn ($q) => $q->where('slug', 'about'))
            ->with([
                'parent',
                'children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')->with([
                    'children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title'),
                ]),
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
        $aboutPages->each(function (Page $page): void {
            $page->children->each(function (Page $child) use ($page): void {
                $child->setRelation('parent', $page);
                $child->children->each->setRelation('parent', $child);
            });
        });

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

        $navItems = $this->loadNavigationItems();
        $siteSettings = SiteSetting::current();

        // Hydrate dynamic dropdowns when admin hasn't created children yet.
        // Keeps hover/flyout visible while still allowing Filament-managed children to override.
        $navItems = $this->hydrateDynamicChildren($navItems, $services, $destinations, $nepal, $aboutPages);

        // Footer: capped 6, hide when empty (no fallback)
        $footerServices = $services->take(6)->values();
        $footerDestinations = $destinations->take(6)->values();
        // Include Nepal in footer destinations list as last item if exists and space remains
        if ($nepal && $footerDestinations->count() < 6) {
            $footerDestinations = $footerDestinations->push($nepal);
        }

        return [
            'aboutPages' => $aboutPages,
            'topLevelPages' => $topLevelPages,
            'services' => $services,
            'destinations' => $destinations,
            'nepal' => $nepal,
            'navItems' => $navItems,
            'siteContact' => $siteSettings->contactBlock(),
            'branding' => $siteSettings->brandingBlock(),
            'footerHeadings' => $siteSettings->footerHeadings(),
            'footerServices' => $footerServices,
            'footerDestinations' => $footerDestinations,
            'navFlyoutStyle' => $siteSettings->navFlyoutStyle(),
        ];
    }

    /**
     * @return Collection<int, NavigationItem>
     */
    protected function loadNavigationItems(): Collection
    {
        try {
            return NavigationItem::query()
                ->visible()
                ->whereNull('parent_id')
                ->ordered()
                ->with([
                    'children' => fn ($q) => $q->visible()->ordered()->with([
                        'children' => fn ($q) => $q->visible()->ordered(),
                    ]),
                ])
                ->get()
                ->each(function (NavigationItem $item): void {
                    $item->children->each(function (NavigationItem $child) use ($item): void {
                        $child->setRelation('parent', $item);
                        $child->children->each(function (NavigationItem $grand) use ($child): void {
                            $grand->setRelation('parent', $child);
                        });
                    });
                });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Attach virtual children from DB when admin hasn't created explicit NavigationItem children.
     * Keeps hover dropdowns functional while remaining fully editable via Filament.
     */
    protected function hydrateDynamicChildren(Collection $navItems, Collection $services, Collection $destinations, ?Destination $nepal, Collection $aboutPages): Collection
    {
        if ($navItems->isEmpty()) {
            return $navItems;
        }

        foreach ($navItems as $item) {
            // Use raw url attribute for detection — resolvedUrl() returns javascript:void(0) for dropdown parents
            $rawUrl = $item->getAttributes()['url'] ?? $item->url ?? $item->resolvedUrl();
            $url = strtolower(rtrim($rawUrl ?? '', '/'));
            $label = strtolower(trim($item->label));

            $isAbout = in_array($url, ['/about-us', '/about'], true) || in_array($label, ['about us', 'about'], true);
            $isNepalTrekking = $url === '/nepal' || $label === 'nepal' || ($label === 'trekking' && in_array($url, ['/nepal', 'javascript:void(0)'], true)) || ($label === 'trekking' && $url === '');
            // Fallback: Trekking label with children that look like Nepal destinations should be treated as Nepal
            if ($label === 'trekking' && ! $isNepalTrekking && $item->children->isNotEmpty() && $item->children->contains(fn ($ch) => str_contains(strtolower($ch->label), 'nepal') || str_contains(strtolower($ch->label), 'langtang'))) {
                $isNepalTrekking = true;
            }
            $isActivities = (in_array($url, ['/ast-services', '/services', '/activities'], true) || in_array($label, ['activities', 'trekking & activities'], true) || ($label === 'trekking' && ! $isNepalTrekking));
            $isDestinations = $url === '/destination' || $label === 'destinations';

            // If item already has children, enrich grandchildren from source models (e.g. Trekking -> Nepal destinations -> their children)
            if ($item->children->isNotEmpty()) {
                if ($isAbout) {
                    $this->enrichChildrenWithPageGrandchildren($item, $aboutPages);
                } elseif ($isNepalTrekking && $nepal) {
                    $this->enrichChildrenWithDestinationGrandchildren($item, $nepal->children);
                } elseif ($isActivities) {
                    $this->enrichChildrenWithServiceGrandchildren($item, $services);
                } elseif ($isDestinations) {
                    $this->enrichChildrenWithDestinationGrandchildren($item, $destinations);
                }

                continue;
            }

            // About Us → pages under "about" (3-level: page -> child -> grandchild)
            if ($isAbout) {
                $virtual = $aboutPages->map(function (Page $page) use ($item) {
                    $child = $this->makeVirtualChild($item, $page->title, $page->getPath(), $page->sort_order ?? 0);
                    if ($page->children->isNotEmpty()) {
                        $grand = $page->children->map(fn (Page $c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
                        $child->setRelation('children', $grand);
                        $grand->each->setRelation('parent', $child);
                    }

                    return $child;
                });
                if ($virtual->isNotEmpty()) {
                    $item->setRelation('children', $virtual);
                }

                continue;
            }

            // Nepal / Trekking with /nepal URL → nepal children (3-level: nepal -> child -> grandchild) - check before Activities to avoid "trekking" label collision
            if ($isNepalTrekking) {
                if ($nepal && $nepal->children->isNotEmpty()) {
                    $virtual = $nepal->children->map(function (Destination $child) use ($item) {
                        $node = $this->makeVirtualChild($item, $child->title, $child->getPath(), $child->sort_order ?? 0);
                        if ($child->children->isNotEmpty()) {
                            $grand = $child->children->map(fn (Destination $c) => $this->makeVirtualChild($node, $c->title, $c->getPath(), $c->sort_order ?? 0));
                            $node->setRelation('children', $grand);
                            $grand->each->setRelation('parent', $node);
                        }

                        return $node;
                    });
                    if ($virtual->isNotEmpty()) {
                        $item->setRelation('children', $virtual);
                    }
                }

                continue;
            }

            // Activities / Trekking & Activities → services
            if ($isActivities) {
                $virtual = $services->map(function (Service $service) use ($item) {
                    $child = $this->makeVirtualChild($item, $service->title, $service->getPath(), $service->sort_order ?? 0);
                    if ($service->children->isNotEmpty()) {
                        $grand = $service->children->map(fn (Service $c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
                        $child->setRelation('children', $grand);
                        // Hydrate grandchildren's parent
                        $grand->each->setRelation('parent', $child);
                    }

                    return $child;
                });
                if ($virtual->isNotEmpty()) {
                    $item->setRelation('children', $virtual);
                }

                continue;
            }

            // Destinations → destinations (excluding Nepal)
            if ($isDestinations) {
                $virtual = $destinations->map(function (Destination $dest) use ($item) {
                    $child = $this->makeVirtualChild($item, $dest->title, $dest->getPath(), $dest->sort_order ?? 0);
                    if ($dest->children->isNotEmpty()) {
                        $grand = $dest->children->map(fn (Destination $c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
                        $child->setRelation('children', $grand);
                        $grand->each->setRelation('parent', $child);
                    }

                    return $child;
                });
                if ($virtual->isNotEmpty()) {
                    $item->setRelation('children', $virtual);
                }

                continue;
            }
        }

        return $navItems;
    }

    private function enrichChildrenWithDestinationGrandchildren(NavigationItem $parent, Collection $destinations): void
    {
        $map = $destinations->keyBy(fn ($d) => strtolower(trim($d->title)));
        foreach ($parent->children as $child) {
            if ($child->children->isNotEmpty()) {
                continue;
            }
            $key = strtolower(trim($child->label));
            $dest = $map->get($key);
            if (! $dest || $dest->children->isEmpty()) {
                continue;
            }
            $grand = $dest->children->map(fn ($c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
            $child->setRelation('children', $grand);
            $grand->each->setRelation('parent', $child);
        }
    }

    private function enrichChildrenWithServiceGrandchildren(NavigationItem $parent, Collection $services): void
    {
        $map = $services->keyBy(fn ($s) => strtolower(trim($s->title)));
        foreach ($parent->children as $child) {
            if ($child->children->isNotEmpty()) {
                continue;
            }
            $key = strtolower(trim($child->label));
            $svc = $map->get($key);
            if (! $svc || $svc->children->isEmpty()) {
                continue;
            }
            $grand = $svc->children->map(fn ($c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
            $child->setRelation('children', $grand);
            $grand->each->setRelation('parent', $child);
        }
    }

    private function enrichChildrenWithPageGrandchildren(NavigationItem $parent, Collection $pages): void
    {
        $map = $pages->keyBy(fn ($p) => strtolower(trim($p->title)));
        foreach ($parent->children as $child) {
            if ($child->children->isNotEmpty()) {
                continue;
            }
            $key = strtolower(trim($child->label));
            $page = $map->get($key);
            if (! $page || $page->children->isEmpty()) {
                continue;
            }
            $grand = $page->children->map(fn ($c) => $this->makeVirtualChild($child, $c->title, $c->getPath(), $c->sort_order ?? 0));
            $child->setRelation('children', $grand);
            $grand->each->setRelation('parent', $child);
        }
    }

    protected function makeVirtualChild(NavigationItem $parent, string $label, string $url, int $sortOrder = 0): NavigationItem
    {
        $child = new NavigationItem([
            'label' => $label,
            'url' => $url,
            'is_visible' => true,
            'open_in_new_tab' => false,
            'sort_order' => $sortOrder,
            'parent_id' => $parent->getKey(),
        ]);
        $child->setRelation('parent', $parent);
        $child->setRelation('children', collect());

        return $child;
    }
}
