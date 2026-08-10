@extends('layouts.app')

@section('title', $destination->title)

@section('content')
    <x-hero
        eyebrow="Destinations"
        :title="$destination->title"
        :lede="$destination->excerpt"
        :image="$destination->cover_image" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                <nav class="space-y-1" aria-label="Destination navigation">
                    <a href="/destination/" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ request()->routeIs('destinations.index') ? 'border-royal text-royal font-semibold' : '' }}">All Destinations</a>
                    @foreach ($navDestinations as $top)
                        <a href="{{ $top->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $destination->slug === $top->slug || $destination->parent_id === $top->id ? 'border-royal text-royal font-semibold' : '' }}">{{ $top->title }}</a>
                    @endforeach
                    @if ($navNepal)
                        <a href="{{ $navNepal->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $destination->slug === $navNepal->slug || $destination->parent_id === $navNepal->id ? 'border-royal text-royal font-semibold' : '' }}">{{ $navNepal->title }}</a>
                    @endif
                    @if ($destination->children->isNotEmpty())
                        <div class="mt-4 border-t border-line pt-4">
                            @foreach ($destination->children as $child)
                                <a href="{{ $child->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $destination->slug === $child->slug ? 'border-royal text-royal font-semibold' : '' }}">{{ $child->title }}</a>
                            @endforeach
                        </div>
                    @endif
                </nav>
            </aside>

            <div class="lg:col-span-9">
                @if ($destination->content)
                    <div class="prose-editorial reveal">
                        {!! $destination->content !!}
                    </div>
                @endif

                @if ($destination->children->isNotEmpty())
                    <div class="mt-14 border-t border-line pt-10">
                        <p class="eyebrow eyebrow-royal">Programs</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            @foreach ($destination->children as $child)
                                <a href="{{ $child->getPath() }}" class="group block rounded-card border border-line bg-paper-soft p-6 transition-all duration-300 hover:-translate-y-1 hover:border-royal hover:shadow-card reveal card-lift">
                                    <h3 class="text-lg font-bold tracking-tight text-ink group-hover:text-royal">{{ $child->title }}</h3>
                                    @if ($child->excerpt)
                                        <p class="mt-2 line-clamp-2 text-sm text-ink-faint">{{ $child->excerpt }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
