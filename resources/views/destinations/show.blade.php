@extends('layouts.app')

@section('title', $destination->title)

@section('content')
    <x-hero
        :title="$destination->title"
        :lede="$destination->excerpt"
        image="/assets/images/cover-all.png" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="relative isolate flex flex-col gap-6 lg:gap-8">
                    @if ($destination->cover_image)
                        <figure class="group relative z-10 w-full reveal">
                            <img src="{{ $destination->cover_image }}" alt="{{ $destination->title }}" class="block h-auto w-full rounded-card border border-line/60 object-cover aspect-[16/9] shadow-card transition-shadow duration-500 ease-out hover:shadow-[0_20px_50px_rgba(0,0,0,0.22)]" loading="lazy" referrerpolicy="no-referrer">
                        </figure>
                    @endif
                    <div class="min-w-0 flex-1 prose-editorial prose-p:leading-8 prose-p:text-[15.5px] sm:prose-p:text-[16px] lg:prose-p:text-[17px] prose-p:tracking-[-0.01em] prose-li:leading-7 prose-headings:leading-tight reveal">
                            @if ($destination->content)
                                {!! $destination->content !!}
                            @else
                                <p class="text-ink-faint">Detailed information is being finalized — please contact our team for a tailored itinerary and latest updates.</p>
                            @endif
                    </div>
                </div>

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

            <aside class="lg:col-span-3 lg:sticky lg:top-28 lg:self-start">
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
        </div>
    </section>
@endsection
