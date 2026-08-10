@extends('layouts.app')

@section('title', 'AST Services')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="AST Services"
        lede="Culture, adventure and jungle safari packages for groups and individuals across Nepal and the Himalaya."
        image="https://adventurespecialist.com.np/wp-content/themes/trekking/images/2.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <x-section-heading
            eyebrow="What we do"
            title="Explore our services"
            lede="Hover or tap a card to flip it and read more about each adventure." />

        @if ($services->isNotEmpty())
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-service-card :service="$service" />
                @endforeach
            </div>
        @else
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['title' => 'Mountain Flight', 'slug' => 'mountain-flight'],
                    ['title' => 'Short Hiking', 'slug' => 'short-hiking'],
                    ['title' => 'Jungle Safari', 'slug' => 'jungle-safari'],
                    ['title' => 'Paragliding', 'slug' => 'paragliding'],
                    ['title' => 'Bungee Jumping', 'slug' => 'bungee-jumping'],
                    ['title' => 'Rafting', 'slug' => 'rafting'],
                ] as $service)
                    <a href="/ast-services/"
                        class="group relative flex aspect-[4/3] items-end overflow-hidden rounded-card bg-pine-deep p-6 text-paper reveal card-lift">
                        <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/90 to-transparent"></div>
                        <h3 class="relative text-xl font-bold tracking-tight">{{ $service['title'] }}</h3>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
