@extends('layouts.app')

@section('title', 'Special Packages')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="AST Special Package Program"
        lede="Signature multi-day programs arranged across Nepal and the Himalaya."
        image="https://adventurespecialist.com.np/wp-content/uploads/2017/02/4.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <x-section-heading
            eyebrow="Signature programs"
            title="Our featured packages" />

        @if ($packages->isNotEmpty())
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>
        @else
            <p class="text-ink-faint">Packages coming soon.</p>
        @endif
    </section>
@endsection
