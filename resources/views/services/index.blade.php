@extends('layouts.app')

@section('title', 'AST Services')

@section('content')
    <x-hero
        title="AST Services"
        lede="Culture, adventure and jungle safari packages for groups and individuals across Nepal and the Himalaya."
        image="/assets/images/cover-all.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <x-section-heading
            eyebrow="What we do"
            title="Explore our services"
            lede="Hover or tap a card to flip it and read more about each adventure."
            align="center" />

        @if ($services->isNotEmpty())
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-service-card :service="$service" />
                @endforeach
            </div>
        @else
            <p class="mt-14 text-center text-ink-faint">Our services are being updated — new adventures will appear here shortly. Please contact us for tailored options.</p>
        @endif
    </section>
@endsection
