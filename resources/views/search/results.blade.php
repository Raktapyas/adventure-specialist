@extends('layouts.app')

@section('title', $q !== '' ? 'Search: '.$q : 'Search')

@section('content')
    <x-hero
        :title="$q !== '' ? 'Search results' : 'Search'"
        :lede="$q !== '' ? 'Results for “'.e($q).'” across treks, destinations and travel information.' : 'Search treks, destinations, permits and travel advice.'"
        image="/assets/images/cover-all.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-16 lg:py-20">
        <form method="GET" action="{{ route('search') }}" class="mx-auto flex max-w-2xl items-center gap-3">
            <input name="q" type="search" value="{{ $q }}" placeholder="Search treks, permits, best time..." class="w-full rounded-card border border-line bg-paper-soft px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20" autofocus />
            <button type="submit" class="shrink-0 rounded-card bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-accent-dark">Search</button>
        </form>

        @if ($q === '')
            <p class="mt-10 text-center text-sm text-ink-faint">Try “Everest”, “Annapurna”, “permits” or “best time to visit”.</p>
        @else
            @php $total = $services->count() + $destinations->count() + $pages->count() + ($packages ?? collect())->count(); @endphp

            @if ($total === 0)
                <div class="mt-12 rounded-card border border-line bg-paper-soft p-8 text-center">
                    <p class="text-lg font-semibold text-ink">No results for “{{ $q }}”</p>
                    <p class="mt-2 text-sm text-ink-faint">Try a broader term like “trek”, “permits”, “Chitwan” or “best time”.</p>
                </div>
            @else
                <p class="mt-10 text-sm font-semibold text-ink-faint">{{ $total }} {{ Str::plural('result', $total) }} found</p>

                @if ($services->isNotEmpty())
                    <div class="mt-10">
                        <h2 class="text-lg font-bold tracking-tight text-ink">Trekking &amp; Activities</h2>
                        <p class="mt-1 text-sm text-ink-faint">{{ $services->count() }} matches</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($services as $service)
                                <x-service-card :service="$service" />
                            @endforeach
                        </div>
                        <div class="mt-4 text-right">
                            <a href="/ast-services/" class="text-sm font-semibold text-accent hover:text-accent-dark">Browse all Trekking &amp; Activities →</a>
                        </div>
                    </div>
                @endif

                @if ($destinations->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="text-lg font-bold tracking-tight text-ink">Destinations</h2>
                        <p class="mt-1 text-sm text-ink-faint">{{ $destinations->count() }} matches</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($destinations as $destination)
                                <x-destination-card :destination="$destination" />
                            @endforeach
                        </div>
                        <div class="mt-4 text-right">
                            <a href="/destination/" class="text-sm font-semibold text-accent hover:text-accent-dark">Browse all Destinations →</a>
                        </div>
                    </div>
                @endif

                @if ($pages->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="text-lg font-bold tracking-tight text-ink">Travel information</h2>
                        <p class="mt-1 text-sm text-ink-faint">{{ $pages->count() }} matches</p>
                        <ul class="mt-6 divide-y divide-line rounded-card border border-line bg-paper-soft">
                            @foreach ($pages as $page)
                                <li>
                                    <a href="{{ $page->getPath() }}" class="block px-6 py-4 transition-colors hover:bg-paper hover:text-accent">
                                        <span class="font-semibold text-ink">{{ $page->title }}</span>
                                        @if ($page->excerpt)
                                            <span class="mt-1 line-clamp-2 block text-sm text-ink-faint">{{ Str::limit(strip_tags($page->excerpt), 140) }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (($packages ?? collect())->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="text-lg font-bold tracking-tight text-ink">Packages</h2>
                        <p class="mt-1 text-sm text-ink-faint">{{ $packages->count() }} matches</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($packages as $package)
                                <x-package-card :package="$package" />
                            @endforeach
                        </div>
                        <div class="mt-4 text-right">
                            <a href="/special-package/" class="text-sm font-semibold text-accent hover:text-accent-dark">Browse all Packages →</a>
                        </div>
                    </div>
                @endif
            @endif
        @endif
    </section>
@endsection
