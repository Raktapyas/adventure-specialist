@props([
    'src',
    'type' => 'image',
    'alt' => '',
    'loading' => 'lazy',
])

@if ($type === 'video')
    {{-- Responsive background/inline video: silent, looping, inline on iOS --}}
    <video {{ $attributes->merge(['class' => '']) }} src="{{ $src }}" autoplay muted loop playsinline preload="metadata"></video>
@else
    <img {{ $attributes->merge(['class' => '']) }} src="{{ $src }}" alt="{{ $alt }}" loading="{{ $loading }}" referrerpolicy="no-referrer">
@endif
