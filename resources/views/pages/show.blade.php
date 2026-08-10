@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <x-hero
        eyebrow="About Adventure Specialist Travel"
        :title="$page->title"
        :lede="$page->excerpt"
        :image="$page->cover_image" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            {{-- Sidebar / sub-navigation --}}
            <aside class="lg:col-span-3">
                <nav class="space-y-1" aria-label="About sub-pages">
                    <a href="/about-us/" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ request()->routeIs('pages.index') ? 'border-royal text-royal font-semibold' : '' }}">About Us</a>
                    @foreach ($navAboutPages as $sub)
                        <a href="{{ $sub->getPath() }}" class="block border-l-2 border-line px-4 py-2 text-sm text-ink-soft transition-colors hover:border-royal hover:text-royal {{ $page->slug === $sub->slug ? 'border-royal text-royal font-semibold' : '' }}">{{ $sub->title }}</a>
                    @endforeach
                </nav>
            </aside>

            {{-- Body --}}
            <div class="lg:col-span-9">
                @if ($page->content)
                    <div class="prose-editorial reveal">
                        {!! $page->content !!}
                    </div>
                @else
                    <p class="text-ink-faint">Content coming soon.</p>
                @endif

                @if ($page->children->isNotEmpty())
                    <div class="mt-14 border-t border-line pt-10">
                        <p class="eyebrow eyebrow-royal">Related</p>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($page->children as $child)
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
