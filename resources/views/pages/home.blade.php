@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Hero --}}
    <x-hero
        eyebrow="Adventure Specialist Travel Pvt. Ltd."
        title="The Himalayas, thoughtfully arranged."
        lede="Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar."
        image="https://adventurespecialist.com.np/wp-content/themes/trekking/images/1.jpg">
        <div class="flex flex-wrap gap-3">
            <a href="/ast-services/" class="btn btn-royal">Explore Services</a>
            <a href="/contact/#enquiry" class="btn btn-outline">Plan a Trip</a>
        </div>
    </x-hero>

    {{-- Welcome / intro --}}
    <section class="mx-auto max-w-[1240px] px-6 py-24 lg:py-32">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-4">
                <x-section-heading
                    eyebrow="Namaste"
                    title="Welcome to our Website"
                    lede="Welcome to the Website of Adventure Specialist Travel. We hope it will become the home to your travel &amp; tour services, you need for Nepal, Tibet, Bhutan, Myanmar and Sikkim." />
            </div>
            <div class="lg:col-span-8">
                <div class="prose-editorial reveal">
                    <p>Our services include:</p>
                    <ul>
                        <li>Culture, Adventure &amp; Jungle Safari Packages for Groups and Individuals</li>
                        <li>Sightseeing tours in &amp; around Kathmandu Valley</li>
                        <li>Arrangement for incentive tours</li>
                    </ul>
                    <p>
                        Adventure Specialist Travel is a dedicated travel and tour company committed to sharing the richness of the Himalayas — from the warm valleys of Nepal to the high plateaus of Tibet and Bhutan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- AST Services grid (flip cards) --}}
    <section class="border-y border-line bg-paper-soft/60">
        <div class="mx-auto max-w-[1240px] px-6 py-24 lg:py-28">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-heading
                    eyebrow="What we do"
                    title="AST Services"
                    lede="Culture, adventure and wildlife — arranged for groups and individuals across the Himalaya." />
                <a href="/ast-services/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                    View all services
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </a>
            </div>

            @if ($services->isNotEmpty())
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services->take(6) as $service)
                        <x-service-card :service="$service" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Counters band (real, data-derived statistics) --}}
    <section class="relative overflow-hidden bg-pine-deep text-paper">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-royal/10 blur-3xl"></div>
        <div class="mx-auto max-w-[1240px] px-6 py-16 lg:py-20">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="text-center reveal">
                        <p class="text-4xl font-extrabold tracking-tight text-paper lg:text-5xl">
                            <span class="count" data-count="{{ $stat['value'] }}" data-suffix="{{ $stat['suffix'] }}">0</span>
                        </p>
                        <p class="mt-3 text-sm font-semibold uppercase tracking-[0.18em] text-royal-bright">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- About / Why choose us --}}
    <section class="mx-auto max-w-[1240px] px-6 py-24 lg:py-32">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-5">
                <x-section-heading
                    eyebrow="About us"
                    title="Why Choose AST?"
                    lede="Adventure Specialist Travel is very concerned about your comfort, your safety and the quality of your time in the mountains." />
            </div>
            <div class="lg:col-span-7">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="rounded-card border border-line bg-paper-soft p-8 reveal card-lift">
                        <p class="eyebrow eyebrow-royal">01</p>
                        <h3 class="mt-3 text-lg font-bold tracking-tight">Hygienic food &amp; water</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink-faint">AST is very concerned about your fooding or eateries in Nepal, when you are traveling here. It tries to suggest you better &amp; hygienic places, where you can go and eat, however, most of the Restaurants, which are meant for tourists, are, of course.</p>
                    </div>
                    <div class="rounded-card border border-line bg-paper-soft p-8 reveal card-lift">
                        <p class="eyebrow eyebrow-royal">02</p>
                        <h3 class="mt-3 text-lg font-bold tracking-tight">Friendly Staff</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink-faint">Nepalese are traditionally friendly people and easygoing people. All staffs, either in the field or in office, are very friendly or care for better hospitality. AST Team consists of the staffs, which come from the northern part of the country, where all high mountains are located.</p>
                    </div>
                    <div class="rounded-card border border-line bg-paper-soft p-8 reveal card-lift sm:col-span-2">
                        <p class="eyebrow eyebrow-royal">03</p>
                        <h3 class="mt-3 text-lg font-bold tracking-tight">Environmental Concern</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink-faint">AST briefs all the staffs to make them aware of environmental issues in the areas, they take clients and are very much conscious about it. It is more precisely done, especially, when AST has group to a trekking in the country.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Destinations --}}
    <section class="border-y border-line bg-pine-deep text-paper">
        <div class="mx-auto max-w-[1240px] px-6 py-24 lg:py-28">
            <x-section-heading
                eyebrow="Where we go"
                title="Destinations"
                lede="From the Kathmandu Valley to the roof of the world — five countries, one standard of care."
                dark />

            @if ($destinations->isNotEmpty())
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($destinations as $destination)
                        <x-destination-card :destination="$destination" dark />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Special packages --}}
    <section class="mx-auto max-w-[1240px] px-6 py-24 lg:py-32">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <x-section-heading
                eyebrow="Signature programs"
                title="AST Special Package Program" />
            <a href="/special-package/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                View all packages
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </a>
        </div>

        @if ($packages->isNotEmpty())
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Gallery preview --}}
    @if ($galleryImages->isNotEmpty())
        <section class="border-t border-line bg-paper-soft/60">
            <div class="mx-auto max-w-[1240px] px-6 py-24 lg:py-28">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-section-heading eyebrow="Moments" title="AST Photo Gallery" />
                    <a href="/gallery/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                        View gallery
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
                <div class="mt-14 grid grid-cols-2 gap-4 lg:grid-cols-6">
                    @foreach ($galleryImages as $image)
                        <a href="/gallery/" class="group aspect-square overflow-hidden rounded-card reveal img-zoom {{ $loop->first ? 'col-span-2 row-span-2' : '' }}">
                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? 'Gallery image' }}" loading="lazy"
                                class="h-full w-full object-cover">
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA strip --}}
    <section class="mx-auto max-w-[1240px] px-6 py-20">
        <div class="reveal flex flex-col items-start justify-between gap-6 overflow-hidden rounded-card bg-pine-deep p-10 text-paper sm:p-14 lg:flex-row lg:items-center">
            <div class="relative">
                <p class="eyebrow text-paper/60">Ready when you are</p>
                <h2 class="display-serif mt-3 text-3xl text-paper sm:text-4xl">Let us arrange your Himalayan holiday.</h2>
            </div>
            <a href="/contact/#enquiry" class="btn btn-royal shrink-0">
                Plan a Trip
            </a>
        </div>
    </section>
@endsection
