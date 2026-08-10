@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="AST Photo Gallery"
        lede="Moments from the mountains, the jungles and the valleys we call home."
        image="/assets/images/gallery/20170320_2059091-1024x576.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        @if ($images->isNotEmpty())
            <div class="columns-1 gap-5 sm:columns-2 lg:columns-3">
                @foreach ($images as $image)
                    <figure class="group mb-5 break-inside-avoid reveal">
                        <a href="{{ $image->image_url }}" target="_blank" rel="noopener"
                            class="block overflow-hidden rounded-card img-zoom">
                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? 'Gallery image' }}" loading="lazy"
                                class="w-full object-cover">
                        </a>
                        @if ($image->caption)
                            <figcaption class="mt-3 text-sm text-ink-faint">{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @else
            <p class="text-ink-faint">Gallery images coming soon.</p>
        @endif
    </section>
@endsection
