<?php

use App\Models\Destination;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (! Schema::hasTable('navigation_items')) {
            return;
        }

        if (! Schema::hasTable('services') || ! Schema::hasTable('destinations') || ! Schema::hasTable('pages')) {
            return;
        }

        $this->seedChildrenForUrl('/ast-services/', fn () => Service::published()->whereNull('parent_id')->orderBy('sort_order')->orderBy('title')->get(), fn ($s) => [$s->title, $s->getPath(), $s->sort_order ?? 0]);
        $this->seedChildrenForUrl('/destination', fn () => Destination::published()->whereNull('parent_id')->where('slug', '!=', 'nepal')->orderBy('sort_order')->orderBy('title')->get(), fn ($d) => [$d->title, $d->getPath(), $d->sort_order ?? 0]);
        $this->seedChildrenForUrl('/nepal', function () {
            $nepal = Destination::published()->where('slug', 'nepal')->with(['children' => fn ($q) => $q->published()->orderBy('sort_order')->orderBy('title')])->first();

            return $nepal?->children ?? collect();
        }, fn ($c) => [$c->title, $c->getPath(), $c->sort_order ?? 0]);
        $this->seedChildrenForUrl('/about-us', fn () => Page::published()->whereHas('parent', fn ($q) => $q->where('slug', 'about'))->orderBy('sort_order')->orderBy('title')->get(), fn ($p) => [$p->title, $p->getPath(), $p->sort_order ?? 0]);
        // Fallback label-based for renamed Activities
        $this->seedChildrenForLabel('Activities', fn () => Service::published()->whereNull('parent_id')->orderBy('sort_order')->orderBy('title')->get(), fn ($s) => [$s->title, $s->getPath(), $s->sort_order ?? 0]);
    }

    private function seedChildrenForUrl(string $url, callable $fetch, callable $map): void
    {
        $parent = NavigationItem::where('url', $url)->orWhere('url', rtrim($url, '/').'/')->first();
        if (! $parent || $parent->children()->exists()) {
            return;
        }
        $items = $fetch();
        if ($items->isEmpty()) {
            return;
        }
        foreach ($items as $item) {
            [$label, $path, $sort] = $map($item);
            NavigationItem::create([
                'parent_id' => $parent->id,
                'label' => $label,
                'url' => $path,
                'type' => 'custom',
                'sort_order' => $sort,
                'is_visible' => true,
                'open_in_new_tab' => false,
            ]);
        }
    }

    private function seedChildrenForLabel(string $label, callable $fetch, callable $map): void
    {
        $parent = NavigationItem::whereRaw('LOWER(label) = ?', [strtolower($label)])->first();
        if (! $parent || $parent->children()->exists()) {
            return;
        }
        // Only seed if URL-based seeding didn't already populate this parent
        if (NavigationItem::where('parent_id', $parent->id)->exists()) {
            return;
        }
        $items = $fetch();
        if ($items->isEmpty()) {
            return;
        }
        foreach ($items as $item) {
            [$l, $p, $s] = $map($item);
            NavigationItem::create([
                'parent_id' => $parent->id,
                'label' => $l,
                'url' => $p,
                'type' => 'custom',
                'sort_order' => $s,
                'is_visible' => true,
                'open_in_new_tab' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep seeded children; no destructive rollback.
    }
};
