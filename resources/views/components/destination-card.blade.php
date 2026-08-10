@props(['destination', 'dark' => false])

<a href="{{ $destination->getPath() }}"
    class="group relative block overflow-hidden rounded-card reveal card-lift">
    <div class="relative aspect-[3/4] overflow-hidden rounded-card img-zoom">
        @if ($destination->cover_image)
            <img src="{{ $destination->cover_image }}" alt="{{ $destination->title }}" loading="lazy"
                class="h-full w-full object-cover">
        @else
            <div class="h-full w-full bg-gradient-to-br from-pine via-moss to-pine-deep"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/15 to-transparent"></div>

        {{-- Category pill --}}
        <span class="absolute left-4 top-4 rounded px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white bg-royal">
            Destination
        </span>

        {{-- Title bar --}}
        <div class="absolute inset-x-0 bottom-0 p-5">
            <h3 class="text-xl font-extrabold tracking-tight text-paper">{{ $destination->title }}</h3>
            @if ($destination->children->isNotEmpty())
                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-paper/70">{{ $destination->children->count() }} {{ Str::plural('program', $destination->children->count()) }}</p>
            @endif
            <span class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-paper opacity-0 transition-all duration-300 group-hover:opacity-100">
                View destination
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform group-hover:translate-x-1"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </span>
        </div>
    </div>
</a>
