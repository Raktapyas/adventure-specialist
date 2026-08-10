@extends('layouts.app')

@section('title', $service->title)

@section('content')
    <x-hero
        eyebrow="AST Services"
        :title="$service->title"
        :lede="$service->excerpt"
        :image="$service->cover_image" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            {{-- Sidebar --}}
            <aside class="lg:col-span-3">
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

            {{-- Body --}}
            <div class="lg:col-span-9">
                @if ($service->content)
                    <div class="prose-editorial reveal">
                        {!! $service->content !!}
                    </div>
                @else
                    <p class="text-ink-faint">Content coming soon.</p>
                @endif

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
        </div>
    </section>
@endsection
