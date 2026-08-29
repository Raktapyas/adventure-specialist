<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return view('search.results', [
                'q' => '',
                'services' => collect(),
                'destinations' => collect(),
                'pages' => collect(),
            ]);
        }

        $q = Str::substr($q, 0, 100);
        $like = $this->escapedLike($q);

        $services = Service::published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(12)
            ->get();

        $destinations = Destination::published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(12)
            ->get();

        $pages = Page::published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(12)
            ->get();

        $packages = Package::published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(12)
            ->get();

        return view('search.results', [
            'q' => $q,
            'services' => $services,
            'destinations' => $destinations,
            'pages' => $pages,
            'packages' => $packages,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $q = Str::substr($q, 0, 100);

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $q));
        $cacheKey = 'suggest:'.Str::lower($normalized);

        $results = Cache::remember($cacheKey, 300, function () use ($normalized): array {
            $like = $this->escapedLike($normalized);

            $services = Service::published()
                ->where(function ($query) use ($like): void {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content', 'like', $like);
                })
                ->with('parent.parent')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'parent_id', 'excerpt']);

            $destinations = Destination::published()
                ->where(function ($query) use ($like): void {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content', 'like', $like);
                })
                ->with('parent.parent')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(3)
                ->get(['id', 'title', 'slug', 'parent_id', 'excerpt']);

            $pages = Page::published()
                ->where(function ($query) use ($like): void {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content', 'like', $like);
                })
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(4)
                ->get(['id', 'title', 'slug', 'parent_id', 'excerpt']);

            $packages = Package::published()
                ->where(function ($query) use ($like): void {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content', 'like', $like);
                })
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(3)
                ->get(['id', 'title', 'slug', 'excerpt']);

            $items = [];

            foreach ($services as $service) {
                $items[] = [
                    'type' => 'Trekking & Activities',
                    'title' => $service->title,
                    'url' => $service->getPath(),
                    'excerpt' => Str::limit(strip_tags((string) $service->excerpt), 70),
                ];
            }

            foreach ($destinations as $destination) {
                $items[] = [
                    'type' => 'Destinations',
                    'title' => $destination->title,
                    'url' => $destination->getPath(),
                    'excerpt' => Str::limit(strip_tags((string) $destination->excerpt), 70),
                ];
            }

            foreach ($pages as $page) {
                $items[] = [
                    'type' => 'Travel Info',
                    'title' => $page->title,
                    'url' => $page->getPath(),
                    'excerpt' => Str::limit(strip_tags((string) $page->excerpt), 70),
                ];
            }

            foreach ($packages as $package) {
                $items[] = [
                    'type' => 'Packages',
                    'title' => $package->title,
                    'url' => $package->getPath(),
                    'excerpt' => Str::limit(strip_tags((string) $package->excerpt), 70),
                ];
            }

            return $items;
        });

        return response()->json($results)->header('Cache-Control', 'public, max-age=60');
    }

    private function escapedLike(string $value): string
    {
        return '%'.addcslashes($value, '%_\\').'%';
    }
}
