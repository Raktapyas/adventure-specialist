@props(['eyebrow' => null, 'title', 'lede' => null, 'image' => null])

<section class="relative flex min-h-[78vh] items-end overflow-hidden bg-pine-deep pt-32 pb-20 text-paper sm:items-center lg:min-h-[82vh]">
    @if ($image)
        <div class="absolute inset-0">
            <img src="{{ $image }}" alt="" class="h-full w-full animate-slow-zoom object-cover object-center brightness-[1.15] saturate-[0.95]" loading="eager" referrerpolicy="no-referrer">
            {{-- Lighter overlay: imagery visible, bottom-weighted scrim keeps text readable --}}
            <div class="absolute inset-0 bg-gradient-to-t from-pine-deep via-pine-deep/30 to-pine-deep/10"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-pine-deep/45 via-pine-deep/10 to-transparent"></div>
        </div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-pine-deep via-pine to-moss"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/80 to-transparent"></div>
    @endif

    <div class="relative mx-auto w-full max-w-[1240px] px-6">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <p class="animate-fade-up text-[0.8125rem] font-bold uppercase tracking-[0.22em] text-royal-bright" style="animation-delay: 120ms">
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="display mt-5 text-4xl leading-[1.05] text-paper sm:text-5xl lg:text-6xl split-reveal">
                {{ $title }}
            </h1>

            @if ($lede)
                <p class="mt-6 max-w-2xl animate-fade-up text-lg leading-relaxed text-paper/85" style="animation-delay: 260ms">
                    {{ $lede }}
                </p>
            @endif

            @isset($slot)
                <div class="mt-8 animate-fade-up" style="animation-delay: 380ms">
                    {{ $slot }}
                </div>
            @endisset
        </div>
    </div>
</section>
