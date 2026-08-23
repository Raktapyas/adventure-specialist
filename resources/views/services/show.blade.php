@extends('layouts.app')

@section('title', $service->title)

@section('content')
    <x-hero
        :title="$service->title"
        :lede="$service->excerpt"
        image="/assets/images/cover-all.png" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            {{-- Body --}}
            <div class="lg:col-span-9">
                <div class="relative isolate flex flex-col sm:flex-row gap-6 lg:gap-8 items-start">
                    @if ($service->cover_image)
                        <figure class="group relative z-10 shrink-0 w-52 sm:w-60 md:w-64 lg:w-72 xl:w-80 reveal">
                            <img src="{{ $service->cover_image }}" alt="{{ $service->title }}" class="block h-auto w-full origin-top-left cursor-zoom-in rounded-card border border-line/60 object-cover aspect-[4/3] shadow-card transition-all duration-500 ease-out will-change-transform hover:z-30 hover:shadow-[0_20px_50px_rgba(0,0,0,0.22)] group-hover:z-30 group-hover:shadow-[0_20px_50px_rgba(0,0,0,0.22)] md:group-hover:scale-150 md:hover:scale-150 active:scale-110" loading="lazy" referrerpolicy="no-referrer">
                        </figure>
                    @endif
                    <div class="min-w-0 flex-1 prose-editorial prose-p:leading-8 prose-p:text-[15.5px] sm:prose-p:text-[16px] lg:prose-p:text-[17px] prose-p:tracking-[-0.01em] prose-li:leading-7 prose-headings:leading-tight reveal">
                        @if ($service->content)
                                {!! $service->content !!}
                            @else
                                <p class="text-ink-faint">Detailed information is being finalized — please contact our team for a tailored itinerary and latest updates.</p>
                            @endif
                    </div>
                </div>

                @if ($service->children->isNotEmpty())
                    <div class="mt-14 border-t border-line pt-10">
                        <p class="eyebrow eyebrow-royal">Experiences</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            @foreach ($service->children as $child)
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

            {{-- Sidebar (right) - sticky --}}
            <aside class="lg:col-span-3 lg:sticky lg:top-28 lg:self-start">
                <nav class="space-y-1" aria-label="Service navigation">
                    <a href="/ast-services/" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ request()->routeIs('services.index') ? 'border-royal text-royal font-semibold' : '' }}">All Services</a>
                    @foreach ($navServices as $top)
                        <a href="{{ $top->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $service->slug === $top->slug || $service->parent_id === $top->id ? 'border-royal text-royal font-semibold' : '' }}">{{ $top->title }}</a>
                    @endforeach
                    @if ($service->children->isNotEmpty())
                        <div class="mt-4 border-t border-line pt-4">
                            @foreach ($service->children as $child)
                                <a href="{{ $child->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $service->slug === $child->slug ? 'border-royal text-royal font-semibold' : '' }}">{{ $child->title }}</a>
                            @endforeach
                        </div>
                    @endif
                </nav>
            </aside>
        </div>
    </section>
@endsection
