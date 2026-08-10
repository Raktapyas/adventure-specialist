@extends('layouts.app')

@section('title', 'Destinations')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="Destinations"
        lede="Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar."
        image="/assets/images/banners/3.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <x-section-heading
            eyebrow="Where we go"
            title="Choose your destination"
            lede="Five countries, one standard of care — from the Kathmandu Valley to the roof of the world." />

        @if ($destinations->isNotEmpty())
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($destinations as $destination)
                    <x-destination-card :destination="$destination" />
                @endforeach
            </div>
        @else
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (['Tibet', 'Bhutan', 'Sikkim', 'Myanmar'] as $destination)
                    <a href="/destination/" class="group reveal card-lift">
                        <div class="relative flex aspect-[3/4] items-end overflow-hidden rounded-card bg-pine-deep p-6 text-paper">
                            <h3 class="text-xl font-bold tracking-tight">{{ $destination }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
