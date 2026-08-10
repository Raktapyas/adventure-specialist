@props(['package'])

<a href="{{ $package->getPath() }}"
    class="group relative block overflow-hidden rounded-card border border-line bg-paper-soft shadow-card transition-all duration-500 hover:shadow-card-hover hover:-translate-y-1 reveal">
    <div class="relative aspect-[4/3] overflow-hidden img-zoom">
        @if ($package->cover_image)
            <img src="{{ $package->cover_image }}" alt="{{ $package->title }}" loading="lazy"
                class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-pine to-pine-deep">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-12 w-12 text-paper/50"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            </div>
        @endif
        @if ($package->duration_days)
            <span class="absolute left-4 top-4 rounded bg-royal px-3 py-1 text-xs font-semibold tracking-wide text-white">{{ $package->duration_days }} Days</span>
        @endif
    </div>
    <div class="p-6">
        <h3 class="text-lg font-bold tracking-tight text-ink transition-colors group-hover:text-royal">{{ $package->title }}</h3>
        @if ($package->excerpt)
            <p class="mt-2 line-clamp-2 text-sm text-ink-faint">{{ $package->excerpt }}</p>
        @endif
        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-royal">
            Read more
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform group-hover:translate-x-1"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
        </span>
    </div>
</a>
