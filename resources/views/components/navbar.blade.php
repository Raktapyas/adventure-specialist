@props(['active' => null])

<header x-data="{
        open: false,
        scrolled: false,
        about: false,
        services: false,
        destination: false,
        activeHome: @js(request()->routeIs('home')),
        activePages: @js(request()->routeIs('pages.*')),
        activeServices: @js(request()->routeIs('services.*')),
        activeDestinations: @js(request()->routeIs('destinations.*')),
        activeGallery: @js(request()->routeIs('gallery')),
        activeContact: @js(request()->routeIs('contact.*')),
    }"
    @scroll.window="scrolled = window.scrollY > 12"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
    :class="scrolled ? 'bg-paper/95 backdrop-blur-md shadow-nav' : 'bg-transparent'">

    {{-- Top contact strip (reference: solid royal-blue bar, collapses on scroll) --}}
    <div class="hidden overflow-hidden bg-royal text-paper transition-all duration-300 lg:block"
        :class="scrolled ? 'lg:hidden opacity-0 -translate-y-4 max-h-0' : 'opacity-100 translate-y-0 max-h-12'">
        <div class="mx-auto flex max-w-[1240px] items-center justify-between px-6 py-2 text-[0.75rem] tracking-wide">
            <div class="flex items-center gap-6">
                <a href="tel:+97715173283" class="inline-flex items-center gap-2 font-medium text-paper/95 transition-colors hover:text-paper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                    +977 1 5173283
                </a>
                <a href="tel:+9779851024546" class="inline-flex items-center gap-2 font-medium text-paper/95 transition-colors hover:text-paper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                    +977 9851024546 (M.D. Raj K. Shrestha)
                </a>
                <a href="mailto:adventurespecialisttravel@gmail.com" class="inline-flex items-center gap-2 font-medium text-paper/95 transition-colors hover:text-paper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    adventurespecialisttravel@gmail.com
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="https://www.facebook.com/Adventure-Specialist-Travel-PvtLtd-318003508387072" target="_blank" rel="noopener"
                    class="flex h-7 w-7 items-center justify-center rounded-full border border-paper/70 text-paper/95 transition-all hover:bg-paper hover:text-royal" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5"><path d="M13.5 9H16l.5-2.5H13.5V5.25c0-.65.35-1.25 1.35-1.25H16.5V1.5S15.3 1.5 14 1.5c-1.7 0-2.9 1.05-2.9 3v2H8.5V9h2.6v6.5h2.4V9Z" /></svg>
                </a>
                <a href="mailto:adventurespecialisttravel@gmail.com" class="flex h-7 w-7 items-center justify-center rounded-full border border-paper/70 text-paper/95 transition-all hover:bg-paper hover:text-royal" aria-label="Email">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Main navigation --}}
    <div class="mx-auto max-w-[1240px] px-6">
        <nav class="flex items-center justify-between py-5" aria-label="Primary">
            {{-- Brand: real reference logo (white over hero, colored when scrolled) --}}
            <a href="/" class="flex items-center" aria-label="Adventure Specialist Travel — Home">
                <span class="relative block">
                    <img src="{{ asset('images/logo-white.png') }}" alt="" x-show="!scrolled"
                        class="h-11 w-auto sm:h-12">
                    <img src="{{ asset('images/logo.png') }}" alt="Adventure Specialist Travel" x-show="scrolled" x-cloak
                        class="h-11 w-auto sm:h-12">
                </span>
            </a>

            {{-- Desktop links --}}
            <div class="hidden items-center gap-7 lg:flex" :class="scrolled ? 'text-ink-soft' : 'text-paper/90'">
                <a href="/" class="nav-link" :class="activeHome ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">Home</a>

                {{-- About Us --}}
                <div class="relative" @mouseenter="about = true" @mouseleave="about = false">
                    <a href="/about-us/" class="nav-link flex items-center gap-1" :class="activePages ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">
                        About Us
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="about ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div x-show="about" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute left-0 top-full w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm">
                        <a href="/about-us/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">About Us</a>
                        @foreach ($navAboutPages as $page)
                            <a href="{{ $page->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">{{ $page->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- AST Services --}}
                <div class="relative" @mouseenter="services = true" @mouseleave="services = false">
                    <a href="/ast-services/" class="nav-link flex items-center gap-1" :class="activeServices ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">
                        AST Services
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="services ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div x-show="services" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute left-0 top-full w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm">
                        <a href="/ast-services/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">All Services</a>
                        @foreach ($navServices as $service)
                            <a href="{{ $service->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">{{ $service->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Destination --}}
                <div class="relative" @mouseenter="destination = true" @mouseleave="destination = false">
                    <a href="/destination/" class="nav-link flex items-center gap-1" :class="activeDestinations ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">
                        Destination
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="destination ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div x-show="destination" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute left-0 top-full w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm">
                        <a href="/destination/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">All Destinations</a>
                        @foreach ($navDestinations as $destination)
                            <a href="{{ $destination->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-royal">{{ $destination->title }}</a>
                        @endforeach
                        @if ($navNepal)
                            <a href="{{ $navNepal->getPath() }}" class="block rounded px-3 py-2 text-sm font-semibold text-ink hover:bg-paper hover:text-royal">{{ $navNepal->title }}</a>
                        @endif
                    </div>
                </div>

                <a href="/gallery/" class="nav-link" :class="activeGallery ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">Gallery</a>
                <a href="/contact/" class="nav-link" :class="activeContact ? (scrolled ? 'text-royal' : 'text-royal-bright') : ''">Contact Us</a>

                <a href="/contact/#enquiry" class="btn btn-royal px-5! py-2.5! text-xs uppercase tracking-wider">
                    Plan a Trip
                </a>
            </div>

            {{-- Mobile toggle --}}
            <button type="button" @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-card transition-colors lg:hidden"
                :class="scrolled ? 'border border-line text-ink' : 'border border-paper/40 text-paper'"
                aria-expanded="false" :aria-expanded="open.toString()" aria-controls="mobile-menu" :aria-label="open ? 'Close menu' : 'Open menu'">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </nav>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak x-transition.opacity.duration.200ms id="mobile-menu"
        class="border-t border-line bg-paper lg:hidden max-h-[calc(100vh-4rem)] overflow-y-auto">
        <nav class="mx-auto max-w-[1240px] space-y-1 px-6 py-6" aria-label="Mobile">
            <a href="/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">Home</a>
            <a href="/about-us/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">About Us</a>
            <a href="/ast-services/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">AST Services</a>
            <a href="/destination/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">Destination</a>
            <a href="/gallery/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">Gallery</a>
            <a href="/contact/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-royal">Contact Us</a>
            <div class="border-t border-line pt-4">
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-ink-faint">Contact</p>
                <a href="tel:+97715173283" class="mt-1 block rounded px-3 py-2 text-sm text-ink-soft hover:text-royal">+977 1 5173283</a>
                <a href="tel:+9779851024546" class="block rounded px-3 py-2 text-sm text-ink-soft hover:text-royal">+977 9851024546</a>
                <a href="mailto:adventurespecialisttravel@gmail.com" class="block rounded px-3 py-2 text-sm text-ink-soft hover:text-royal">adventurespecialisttravel@gmail.com</a>
            </div>
            <a href="/contact/#enquiry" class="btn btn-royal mt-2 w-full">Plan a Trip</a>
        </nav>
    </div>
</header>
