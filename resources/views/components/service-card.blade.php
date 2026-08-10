@props(['service'])

<div class="flip reveal h-full">
    <div class="flip-inner aspect-[4/5]">
        {{-- Front face --}}
        <a href="{{ $service->getPath() }}" class="flip-face flip-front group block overflow-hidden rounded-card bg-paper-soft shadow-card">
            @if ($service->cover_image)
                <img src="{{ $service->cover_image }}" alt="{{ $service->title }}" loading="lazy"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1.2s] ease-out group-hover:scale-[1.06]">
                <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/20 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-pine via-moss to-pine-deep"></div>
            @endif
            <div class="absolute inset-x-0 bottom-0 p-6">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-paper/95 text-royal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                </span>
                <h3 class="mt-4 text-xl font-bold tracking-tight text-paper">{{ $service->title }}</h3>
            </div>
        </a>

        {{-- Back face --}}
        <a href="{{ $service->getPath() }}" class="flip-face flip-back flex flex-col justify-between overflow-hidden rounded-card p-7 bg-gradient-to-br from-royal via-royal-dark to-pine-deep text-paper">
            <div>
                <span class="text-[0.6875rem] font-bold uppercase tracking-[0.2em] text-paper/70">Adventure</span>
                <h3 class="mt-3 text-xl font-extrabold tracking-tight">{{ $service->title }}</h3>
                @if ($service->excerpt)
                    <p class="mt-3 line-clamp-5 text-sm leading-relaxed text-paper/85">{{ $service->excerpt }}</p>
                @endif
            </div>
            <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold">
                Explore
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </span>
        </a>
    </div>
</div>
