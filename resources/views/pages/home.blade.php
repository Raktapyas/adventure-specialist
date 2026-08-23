@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Hero (cinematic slider) --}}
    @php
        $heroSlides = [
            [
                'image' => '/assets/images/banners/1.jpg',
                'eyebrow' => 'Adventure Specialist Travel Pvt. Ltd.',
                'title' => 'The Himalayas, thoughtfully arranged.',
                'lede' => 'Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar.',
                'kenburns' => 'animate-hero-zoom-in',
                'ctas' => [
                    ['label' => 'Explore Services', 'href' => '/ast-services/', 'style' => 'royal'],
                    ['label' => 'Plan a Trip', 'href' => '/contact/#enquiry', 'style' => 'outline'],
                ],
            ],
            [
                'image' => '/assets/images/banners/2.jpg',
                'eyebrow' => 'Where we go',
                'title' => 'Five countries, one standard of care.',
                'lede' => 'From the Kathmandu Valley to the roof of the world — culture, adventure and wildlife.',
                'kenburns' => 'animate-hero-pan-right',
                'ctas' => [
                    ['label' => 'Explore Destinations', 'href' => '/destination/', 'style' => 'royal'],
                    ['label' => 'Plan a Trip', 'href' => '/contact/#enquiry', 'style' => 'outline'],
                ],
            ],
            [
                'image' => '/assets/images/banners/3.jpg',
                'eyebrow' => 'Signature programs',
                'title' => 'Treks and tours, arranged around you.',
                'lede' => 'Curated special packages for groups and individuals across the Himalaya.',
                'kenburns' => 'animate-hero-zoom-out',
                'ctas' => [
                    ['label' => 'View Packages', 'href' => '/special-package/', 'style' => 'royal'],
                    ['label' => 'Plan a Trip', 'href' => '/contact/#enquiry', 'style' => 'outline'],
                ],
            ],
        ];
    @endphp

    <x-hero :slides="$heroSlides" />

    {{-- Welcome / intro (premium asymmetric About) --}}
    <section class="mx-auto max-w-[1240px] px-6 py-24 lg:py-32">
        <div class="grid items-center gap-16 lg:grid-cols-12 lg:gap-20">
            <div class="lg:col-span-5">
                <x-section-heading
                    eyebrow="Namaste"
                    title="Where Journeys Become Stories" />

                <p class="reveal mt-5 max-w-xl text-base leading-relaxed text-ink-faint">
                    <strong class="font-semibold text-ink">Bespoke journeys</strong> across
                    <strong class="font-semibold text-ink">Nepal, Bhutan, Sikkim, Tibet and Myanmar</strong>
                    — culture, adventure and wildlife, arranged with care.
                </p>

                <div class="prose-editorial reveal mt-8">
                    <p>Our services include:</p>
                    <ul>
                        <li>Culture, Adventure &amp; Jungle Safari Packages for Groups and Individuals</li>
                        <li>Sightseeing tours in &amp; around Kathmandu Valley</li>
                        <li>Arrangement for incentive tours</li>
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="relative lg:ml-10 lg:translate-y-10">
                    {{-- Gallery card: thumbnail transforms into a full-container promo on hover --}}
                    <div class="group relative reveal reveal-img">
                        {{-- Neutral gallery mat frame (visible by default, fades on hover) --}}
                        <div class="absolute -inset-2 rounded-[calc(var(--radius-card)+8px)] border border-line/70 transition-opacity duration-500 group-hover:opacity-0" aria-hidden="true"></div>

                        {{-- Orange/blue animated border (on hover) --}}
                        <div class="theme-border absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></div>

                        <div class="relative overflow-hidden rounded-card bg-pine-deep/10 shadow-card transition-shadow duration-500 group-hover:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.45)]">
                            {{-- Thumbnail image: slightly inset, expands to fill the container on hover --}}
                            <img
                                src="/assets/images/destinations/Boating_at_Rara.jpg"
                                alt="Boating on Rara Lake, Nepal"
                                class="aspect-[4/3] h-full w-full scale-[0.95] rounded-card object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                loading="lazy"
                            >

                            {{-- Promo overlay: fades in on hover --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/90 via-pine-deep/35 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                                <div class="absolute bottom-5 right-5 max-w-[16rem] translate-y-4 text-right transition-transform duration-500 group-hover:translate-y-0">
                                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.22em] text-royal-bright">The Himalaya Awaits</p>
                                    <p class="mt-1 text-xl font-extrabold leading-tight tracking-tight text-paper">Your Journey Starts Here</p>
                                    <a href="#services" class="mt-3 inline-flex items-center gap-2 rounded-full bg-royal px-5 py-2.5 text-sm font-bold text-paper shadow-[0_8px_20px_-6px_rgba(12,90,219,0.6)] transition-colors duration-300 hover:bg-royal-bright">
                                        Discover Journeys
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Exploration badge: compass + mountain detail, integrated with the image --}}
                        <div class="group/badge absolute bottom-5 left-5">
                            <div class="relative overflow-hidden rounded-2xl border border-paper/15 bg-gradient-to-br from-pine-deep/95 via-pine-deep/75 to-pine-deep/55 px-5 py-4 shadow-[0_18px_40px_-12px_rgba(13,18,14,0.6)] backdrop-blur-md transition-all duration-500 group-hover/badge:-translate-y-1 group-hover/badge:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.75)]">
                                {{-- Mountain silhouette detail --}}
                                <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-8 w-full text-royal/20" viewBox="0 0 200 40" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M0 40 L28 16 L52 30 L80 6 L108 26 L138 14 L168 32 L200 20 L200 40 Z" fill="currentColor" />
                                </svg>

                                <div class="relative flex items-center gap-4">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-royal to-royal-dark text-paper shadow-[0_8px_20px_-6px_rgba(12,90,219,0.65)] transition-transform duration-700 group-hover/badge:rotate-[135deg]" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="m16.5 7.5-3 6-6 3 3-6 6-3Z" fill="currentColor" stroke="none" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-[0.65rem] font-bold uppercase tracking-[0.22em] text-royal-bright">Explore</p>
                                        <p class="mt-0.5 text-lg font-extrabold leading-tight tracking-tight text-paper">Beyond Borders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- AST Services grid (flip cards) --}}
    <section id="services" class="flex min-h-svh flex-col items-center justify-center border-y border-line bg-paper-soft/60">
        <div class="w-full max-w-[1240px] px-6 py-24 lg:py-28">
            <div class="grid items-end gap-6 lg:grid-cols-[1fr_auto_1fr]">
                <div class="hidden lg:block" aria-hidden="true"></div>
                <x-section-heading
                    eyebrow="What we do"
                    title="AST Services"
                    lede="Culture, adventure and wildlife — arranged for groups and individuals across the Himalaya."
                    align="center" />
                <div class="flex justify-start lg:justify-end">
                    <a href="/ast-services/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                        View all services
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
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

    {{-- Counters band (premium scroll-parallax) --}}
    <section class="relative overflow-hidden bg-pine-deep text-paper">
        {{-- Parallax background layer --}}
        <div class="parallax-bg" aria-hidden="true">
            <div class="parallax-bg__inner" data-parallax>
                <img src="/assets/images/banners/1.jpg" alt="" class="h-full w-full object-cover" loading="lazy">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-pine-deep/70 via-pine-deep/40 to-pine-deep/70"></div>
        </div>

        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-royal/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-[1240px] px-6 py-16 lg:py-20">
            <div class="grid gap-10 sm:grid-cols-3">
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

                {{-- Impressive magnetic gallery: thumbnail → full-container promo + mouse parallax --}}
                <div class="group/gallery relative mt-8 reveal reveal-img" data-gallery-magnetic>
                    {{-- Neutral mat frame: visible by default, fades on hover --}}
                    <div class="absolute -inset-2 rounded-[calc(var(--radius-card)+8px)] border border-line/70 transition-opacity duration-500 group-hover/gallery:opacity-0" aria-hidden="true"></div>
                    {{-- Animated theme border: royal → orange on hover --}}
                    <div class="theme-border absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover/gallery:opacity-100" aria-hidden="true"></div>

                    <div class="relative overflow-hidden rounded-card bg-pine-deep/10 shadow-card transition-shadow duration-500 group-hover/gallery:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.45)]">
                        {{-- Thumbnail: slightly inset, expands to fill container on hover + follows cursor --}}
                        <img
                            src="/assets/images/why-why.jpeg"
                            alt="Why choose Adventure Specialist Travel"
                            loading="lazy"
                            data-gallery-img
                            class="aspect-[16/10] h-full w-full scale-[0.96] rounded-card object-cover will-change-transform transition-transform duration-700 ease-out group-hover/gallery:scale-[1.04]"
                        >

                        {{-- Ambient glow behind image (revealed on hover) --}}
                        <div class="pointer-events-none absolute -inset-1 bg-gradient-to-br from-royal/15 via-transparent to-royal-dark/10 opacity-0 blur-xl transition-opacity duration-700 group-hover/gallery:opacity-100" aria-hidden="true"></div>

                        {{-- Promo overlay: fades in on hover, content slides up --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/90 via-pine-deep/40 to-transparent opacity-0 transition-opacity duration-500 group-hover/gallery:opacity-100">
                            {{-- Subtle scan line --}}
                            <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-gradient-to-r from-transparent via-paper/20 to-transparent opacity-0 transition-opacity delay-100 duration-500 group-hover/gallery:opacity-100" aria-hidden="true"></div>

                            <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
                                <div class="flex items-end justify-end">
                                    <a href="/about/" class="hidden shrink-0 translate-y-3 items-center gap-2 rounded-full bg-paper px-5 py-3 text-sm font-bold text-pine-deep shadow-[0_8px_20px_-6px_rgba(0,0,0,0.35)] transition-all duration-500 hover:bg-paper-soft group-hover/gallery:flex group-hover/gallery:translate-y-0 sm:inline-flex">
                                        Our story
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                                    </a>
                                </div>
                                {{-- Mobile CTA (visible only on small) --}}
                                <a href="/about/" class="mt-4 inline-flex items-center gap-2 rounded-full bg-paper px-5 py-2.5 text-sm font-bold text-pine-deep shadow-lg transition-transform duration-500 group-hover/gallery:translate-y-0 sm:hidden">
                                    Our story
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Top shimmer on hover --}}
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-royal-faint/60 to-transparent opacity-0 transition-opacity duration-500 group-hover/gallery:opacity-100" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-7">
                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- 01 Hygienic food & water --}}
                    <div class="group relative flex h-full reveal">
                        <div class="absolute -inset-2 rounded-[calc(var(--radius-card)+8px)] border border-line/70 transition-opacity duration-500 group-hover:opacity-0" aria-hidden="true"></div>
                        <div class="theme-border absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></div>
                        <div class="relative flex h-full w-full flex-col rounded-card bg-paper-soft p-8 shadow-card transition-shadow duration-500 group-hover:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.45)]">
                            <p class="eyebrow eyebrow-royal">01</p>
                            <h3 class="mt-3 text-lg font-bold tracking-tight">Hygienic food & water</h3>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-faint">AST is very concerned about your fooding or eateries in Nepal, when you are traveling here. It tries to suggest you better & hygienic places, where you can go and eat, however, most of the Restaurants, which are meant for tourists, are, of course.</p>
                        </div>
                    </div>

                    {{-- 02 Friendly Staff --}}
                    <div class="group relative flex h-full reveal">
                        <div class="absolute -inset-2 rounded-[calc(var(--radius-card)+8px)] border border-line/70 transition-opacity duration-500 group-hover:opacity-0" aria-hidden="true"></div>
                        <div class="theme-border absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></div>
                        <div class="relative flex h-full w-full flex-col rounded-card bg-paper-soft p-8 shadow-card transition-shadow duration-500 group-hover:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.45)]">
                            <p class="eyebrow eyebrow-royal">02</p>
                            <h3 class="mt-3 text-lg font-bold tracking-tight">Friendly Staff</h3>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-faint">Nepalese are traditionally friendly people and easygoing people. All staffs, either in the field or in office, are very friendly or care for better hospitality. AST Team consists of the staffs, which come from the northern part of the country, where all high mountains are located.</p>
                        </div>
                    </div>

                    {{-- 03 Environmental Concern — natural height, no stretch --}}
                    <div class="group relative reveal sm:col-span-2 sm:self-start">
                        <div class="absolute -inset-2 rounded-[calc(var(--radius-card)+8px)] border border-line/70 transition-opacity duration-500 group-hover:opacity-0" aria-hidden="true"></div>
                        <div class="theme-border absolute -inset-[2px] rounded-card opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></div>
                        <div class="relative rounded-card bg-paper-soft p-8 shadow-card transition-shadow duration-500 group-hover:shadow-[0_24px_50px_-12px_rgba(13,18,14,0.45)]">
                            <p class="eyebrow eyebrow-royal">03</p>
                            <h3 class="mt-3 text-lg font-bold tracking-tight">Environmental Concern</h3>
                            <p class="mt-3 text-sm leading-relaxed text-ink-faint">AST briefs all the staffs to make them aware of environmental issues in the areas, they take clients and are very much conscious about it. It is more precisely done, especially, when AST has group to a trekking in the country.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Destinations --}}
    <section class="relative overflow-hidden border-y border-line bg-pine-deep text-paper">
        {{-- Parallax background layer --}}
        <div class="parallax-bg" aria-hidden="true">
            <div class="parallax-bg__inner" data-parallax>
                <img src="/assets/images/banners/2.jpg" alt="" class="h-full w-full object-cover" loading="lazy">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-pine-deep/70 via-pine-deep/40 to-pine-deep/70"></div>
        </div>

        <div class="relative mx-auto max-w-[1240px] px-6 py-24 lg:py-28">
            <x-section-heading
                eyebrow="Where we go"
                title="Destinations"
                lede="From the Kathmandu Valley to the roof of the world — five countries, one standard of care."
                align="center"
                dark />

            @php $homeDestinations = $destinations->reject(fn($d) => $d->slug === 'nepal')->take(4); @endphp
            @if ($homeDestinations->isNotEmpty())
                {{-- Premium 4-card row (Nepal hidden on home only) — larger, centered, hover enlarges --}}
                <div class="group/row mx-auto mt-14 flex max-w-[1100px] gap-3 sm:gap-4 lg:gap-5 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 snap-x snap-mandatory scrollbar-none"
                     style="scrollbar-width: none; -ms-overflow-style: none;">
                    @foreach ($homeDestinations as $destination)
                        <div class="flex-1 min-w-[62vw] sm:min-w-0 snap-center transition-all duration-700 ease-out
                                    sm:hover:flex-[1.65_1_0%] lg:hover:flex-[1.6_1_0%]
                                    group-hover/row:opacity-60 group-hover/row:brightness-[0.72] hover:!opacity-100 hover:!brightness-100
                                    will-change-[flex]">
                            <x-destination-card :destination="$destination" :delay="$loop->index * 70" variant="row" dark />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Special packages — header centered like AST Services --}}
    <section class="mx-auto max-w-[1240px] px-6 py-24 lg:py-32">
        <div class="grid items-end gap-6 lg:grid-cols-[1fr_auto_1fr]">
            <div class="hidden lg:block" aria-hidden="true"></div>
            <x-section-heading
                eyebrow="Signature programs"
                title="AST Special Package Program"
                align="center" />
            <div class="flex justify-start lg:justify-end">
                <a href="/special-package/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                    View all packages
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                </a>
            </div>
        </div>

        @if ($packages->isNotEmpty())
            <div class="mt-14 grid auto-rows-fr gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" :delay="$loop->index * 60" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Gallery preview — centered header + living fluid grid --}}
    @if ($galleryImages->isNotEmpty())
        <section class="border-t border-line bg-paper-soft/60">
            <div class="mx-auto max-w-[1240px] px-6 pt-24 pb-12 lg:pt-28 lg:pb-14">
                <div class="grid w-full items-end gap-6 sm:grid-cols-[1fr_auto_1fr]">
                    <div class="hidden sm:block" aria-hidden="true"></div>
                    <x-section-heading eyebrow="Moments" title="AST Photo Gallery" align="center" />
                    <div class="flex justify-center sm:justify-end">
                        <a href="/gallery/" class="btn btn-royal px-5! py-3! text-xs uppercase tracking-wider reveal">
                            View gallery
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                {{-- Living gallery — Manthey-inspired fluid container-query grid: hovered expands, pushes neighbors, inactive dim/desaturate --}}
                @php
                    $fluidImages = $galleryImages->take(6)->values();
                    while ($fluidImages->count() < 6) { $fluidImages->push($galleryImages->first()); }
                    $fluidCols = $fluidImages->chunk(2);
                    while ($fluidCols->count() < 3) { $fluidCols->push(collect([$galleryImages->first(), $galleryImages->first()])); }
                @endphp
                <div class="mt-14 w-full [container-type:inline-size]">
                    <div class="group/gallery relative grid h-[78vmin] max-h-[560px] min-h-[380px] w-full gap-3 transition-all duration-500 ease-in-out md:grid-cols-[1fr_1fr_1fr] has-[>div:nth-child(1):hover]:md:grid-cols-[4fr_1fr_1fr] has-[>div:nth-child(2):hover]:md:grid-cols-[1fr_4fr_1fr] has-[>div:nth-child(3):hover]:md:grid-cols-[1fr_1fr_4fr] before:pointer-events-none before:absolute before:inset-0 before:rounded-card before:bg-white/0 before:blur-[60px] hover:before:bg-white/[0.04] before:transition-all before:duration-500 before:ease-in-out lg:gap-4">
                        @foreach ($fluidCols->take(3) as $col)
                            <div class="grid gap-3 transition-all duration-500 ease-in-out grid-rows-[1fr_1fr] has-[article:nth-child(1):hover]:grid-rows-[56fr_18fr] has-[article:nth-child(2):hover]:grid-rows-[18fr_56fr] lg:gap-4">
                                @foreach ($col->take(2) as $image)
                                    <article class="group/item relative overflow-hidden rounded-card cursor-pointer shadow-card reveal" style="transition-delay: {{ $loop->parent->index * 80 + $loop->index * 40 }}ms">
                                        <a href="/gallery/" class="absolute inset-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-royal focus-visible:ring-offset-2" aria-label="View gallery">
                                            <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? 'Gallery image' }}" loading="lazy"
                                                 class="h-full w-full object-cover transition-all duration-500 ease-in-out blur-0 group-hover/gallery:blur-[0.5px] group-hover/gallery:hover:blur-0 brightness-100 group-hover/gallery:brightness-[0.72] group-hover/gallery:hover:brightness-100 contrast-100 group-hover/gallery:contrast-[1.15] group-hover/gallery:hover:contrast-100 saturate-[0.85] group-hover/gallery:saturate-0 group-hover/gallery:hover:saturate-100 scale-100 group-hover/gallery:scale-[0.98] group-hover/gallery:hover:scale-[1.12] will-change-transform">
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- CTA strip --}}
    <section class="mx-auto max-w-[1240px] px-6 pt-10 pb-20 lg:pt-12">
        <div class="reveal relative flex flex-col items-start justify-between gap-6 overflow-hidden rounded-card bg-pine-deep p-10 text-paper sm:p-14 lg:flex-row lg:items-center">
            {{-- Fixed cinematic backdrop --}}
            <div class="parallax-bg" aria-hidden="true">
                <div class="parallax-bg__inner" data-parallax>
                    <img src="/assets/images/banners/3.jpg" alt="" class="h-full w-full object-cover" loading="lazy">
                </div>
                <div class="absolute inset-0 bg-gradient-to-b from-pine-deep/70 via-pine-deep/40 to-pine-deep/70"></div>
            </div>

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
