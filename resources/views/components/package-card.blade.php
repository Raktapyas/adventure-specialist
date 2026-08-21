@props(['package', 'delay' => 0])

@php
    $excerpt = $package->excerpt
        ? Str::limit($package->excerpt, 170)
        : ($package->content ? Str::limit(trim(strip_tags($package->content)), 160) : null);
@endphp

<div class="flip reveal h-full" style="transition-delay: {{ $delay }}ms">
    <div class="flip-inner aspect-[3/5]">
        {{-- Front face — same layout as before: image filling, clean dark overlay, duration + title --}}
        <a href="{{ $package->getPath() }}" class="flip-face flip-front group block overflow-hidden rounded-card bg-paper-soft shadow-card">
            @if ($package->cover_image)
                <img src="{{ $package->cover_image }}" alt="{{ $package->title }}" loading="lazy"
                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1.2s] ease-out group-hover:scale-[1.06]">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/18 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-pine via-moss to-pine-deep"></div>
            @endif

            @if ($package->duration_days)
                <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-royal px-3 py-1.5 text-[0.7rem] font-bold tracking-wide text-white shadow-[0_6px_16px_-6px_rgba(12,90,219,0.6)]">
                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    {{ $package->duration_days }} Days
                </span>
            @endif

            <div class="absolute inset-x-0 bottom-0 p-6">
                <p class="text-[0.68rem] font-bold uppercase tracking-[0.16em] text-white/70">Signature program</p>
                <h3 class="mt-2 font-serif text-[1.2rem] font-medium leading-tight tracking-tight text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.45)]">
                    {{ $package->title }}
                </h3>
                @if ($excerpt)
                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-white/75">{{ $excerpt }}</p>
                @endif
            </div>
        </a>

        {{-- Back face — blue overlay, reveals description & details (same spacing, flip transition) --}}
        <a href="{{ $package->getPath() }}" class="flip-face flip-back flex flex-col justify-between overflow-hidden rounded-card bg-gradient-to-br from-royal via-royal-dark to-pine-deep p-7 text-paper">
            <div>
                <span class="inline-flex items-center gap-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-paper/70">
                    <span class="h-px w-6 bg-paper/30"></span>
                    @if ($package->duration_days) {{ $package->duration_days }} Days &middot; @endif Signature
                </span>
                <h3 class="mt-3 font-serif text-[1.35rem] font-medium leading-tight tracking-tight">{{ $package->title }}</h3>
                @if ($excerpt)
                    <p class="mt-3 line-clamp-5 text-sm leading-relaxed text-paper/85">{{ $excerpt }}</p>
                @else
                    <p class="mt-3 text-sm leading-relaxed text-paper/80">A thoughtfully arranged Himalayan journey — culture, nature and expert care, tailored to your pace.</p>
                @endif
            </div>
            <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-paper text-royal-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </span>
                View itinerary
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </span>
        </a>
    </div>
</div>
