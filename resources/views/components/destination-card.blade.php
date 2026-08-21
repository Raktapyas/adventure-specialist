@props(['destination', 'dark' => false, 'delay' => 0, 'variant' => 'grid'])

@php
    $description = $destination->excerpt
        ? Str::limit($destination->excerpt, 120)
        : ($destination->content ? Str::limit(trim(strip_tags($destination->content)), 110) : null);
    $hasDescription = filled($description);
    $isRow = $variant === 'row';
@endphp

<a href="{{ $destination->getPath() }}"
   style="transition-delay: {{ $delay }}ms"
   class="group/dest relative block overflow-hidden rounded-card bg-black reveal transition-all duration-500 ease-out hover:-translate-y-1.5 hover:shadow-[0_24px_50px_-12px_rgba(0,0,0,0.55)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-royal focus-visible:ring-offset-2 focus-visible:ring-offset-pine-deep {{ $isRow ? 'h-full' : '' }}">
    {{-- Animated theme border — royal→orange on hover --}}
    <span class="theme-border pointer-events-none absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover/dest:opacity-100" aria-hidden="true"></span>

    <div class="relative overflow-hidden rounded-card {{ $isRow ? 'h-[390px] sm:h-[420px] lg:h-[460px] xl:h-[500px]' : 'aspect-[3/4]' }}">
        @if ($destination->cover_image)
            <img src="{{ $destination->cover_image }}" alt="{{ $destination->title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover will-change-transform transition-transform duration-[900ms] ease-out group-hover/dest:scale-[1.08]">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-pine via-moss to-pine-deep"></div>
        @endif

        {{-- Dark / black overlay — premium cinematic, image untouched underneath --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-black/10 opacity-95 transition-opacity duration-500 group-hover/dest:opacity-100"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover/dest:opacity-100"></div>

        {{-- Top hairline shimmer --}}
        <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 transition-opacity duration-500 group-hover/dest:opacity-100" aria-hidden="true"></span>

        {{-- Destination label — top (reference) --}}
        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[0.68rem] font-bold uppercase tracking-[0.16em] text-white backdrop-blur-md transition-colors duration-300 group-hover/dest:border-royal/30 group-hover/dest:bg-royal group-hover/dest:text-white">
            <span class="h-1.5 w-1.5 rounded-full bg-royal-bright group-hover/dest:bg-white transition-colors"></span>
            Destination
        </span>

        {{-- Content: country name near bottom + program count underneath (reference) --}}
        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
            <h3 class="text-xl font-extrabold leading-tight tracking-tight text-white sm:text-[1.35rem] drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">
                {{ $destination->title }}
            </h3>
            @if ($destination->children && $destination->children->isNotEmpty())
                <p class="mt-1.5 inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/70">
                    <span class="h-px w-6 bg-royal-bright/80"></span>
                    {{ $destination->children->count() }} {{ Str::plural('program', $destination->children->count()) }}
                </p>
            @else
                <p class="mt-1.5 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/60">
                    Explore the Himalaya
                </p>
            @endif

            {{-- Divider that grows on hover --}}
            <span class="mt-4 block h-px w-8 bg-white/20 transition-all duration-500 group-hover/dest:w-16 group-hover/dest:bg-royal-bright" aria-hidden="true"></span>

            {{-- Extra details — revealed on hover with premium cinematic transition (transform + opacity only) --}}
            @if ($hasDescription)
                <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 ease-out group-hover/dest:grid-rows-[1fr] group-hover/dest:opacity-100">
                    <div class="overflow-hidden">
                        <p class="pt-3 text-sm leading-relaxed text-white/80 line-clamp-3 translate-y-2 transition-transform duration-500 ease-out group-hover/dest:translate-y-0">
                            {{ $description }}
                        </p>
                    </div>
                </div>
            @else
                <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 ease-out group-hover/dest:grid-rows-[1fr] group-hover/dest:opacity-100">
                    <div class="overflow-hidden">
                        <p class="pt-3 text-sm leading-relaxed text-white/70 translate-y-2 transition-transform duration-500 ease-out group-hover/dest:translate-y-0">
                            Discover culture, adventure and wilderness — handcrafted by Himalayan specialists.
                        </p>
                    </div>
                </div>
            @endif

            {{-- CTA — slides in --}}
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-white opacity-0 translate-y-2 transition-all duration-500 delay-75 group-hover/dest:opacity-100 group-hover/dest:translate-y-0">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-royal text-white shadow-[0_6px_16px_-6px_rgba(12,90,219,0.7)] transition-colors group-hover/dest:bg-royal-bright">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 transition-transform duration-300 group-hover/dest:translate-x-0.5"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </span>
                View destination
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-white/60 transition-all duration-300 group-hover/dest:text-white group-hover/dest:translate-x-1"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </span>
        </div>
    </div>
</a>
