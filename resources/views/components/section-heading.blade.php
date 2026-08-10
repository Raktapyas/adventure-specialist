@props(['eyebrow' => null, 'title', 'lede' => null, 'align' => 'left', 'dark' => false, 'accent' => true])

<div class="{{ $align === 'center' ? 'mx-auto text-center' : '' }} max-w-2xl reveal">
    @if ($eyebrow)
        <p class="eyebrow {{ $dark ? 'text-paper/60' : ($accent ? 'eyebrow-royal' : '') }}">{{ $eyebrow }}</p>
    @endif

    <h2 class="display {{ $dark ? 'text-paper' : '' }} mt-3 text-3xl leading-[1.1] sm:text-4xl lg:text-[2.75rem] split-reveal">
        {{ $title }}
    </h2>

    <div class="{{ $align === 'center' ? 'mx-auto' : '' }} heading-bar mt-5"></div>

    @if ($lede)
        <p class="{{ $dark ? 'text-paper/70' : 'text-ink-faint' }} mt-5 text-base leading-relaxed">{{ $lede }}</p>
    @endif
</div>
