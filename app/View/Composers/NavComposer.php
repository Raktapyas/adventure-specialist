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
        $view->with('navServices', $this->cache['services']);
        $view->with('navDestinations', $this->cache['destinations']);
        $view->with('navNepal', $this->cache['nepal']);
        $view->with('siteContact', $this->cache['siteContact']);
    }

    /**
     * @return array{aboutPages: Collection, services: Collection, destinations: Collection, nepal: ?Destination, siteContact: array<string, ?string>}
     */
    protected function load(): array
    {
        return [
            'aboutPages' => Page::published()
                ->whereHas('parent', fn ($q) => $q->where('slug', 'about'))
                ->with('parent')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'services' => Service::published()
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->with(['children' => fn ($q) => $q->published()])
                ->get(),
            'destinations' => Destination::published()
                ->whereNull('parent_id')
                ->where('slug', '!=', 'nepal')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->with(['children' => fn ($q) => $q->published()])
                ->get(),
            'nepal' => Destination::published()
                ->where('slug', 'nepal')
                ->with(['children' => fn ($q) => $q->published()])
                ->first(),
            'siteContact' => SiteSetting::current()->contactBlock(),
        ];
    }
}
