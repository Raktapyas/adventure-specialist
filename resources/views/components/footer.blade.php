{{-- Decorative silhouette strip crowning the footer — blends into the page background above the dark footer --}}
<div class="bg-paper" aria-hidden="true">
    <img src="/assets/images/footer_bg.avif" alt="" class="h-auto w-full" loading="lazy">
</div>

<footer class="relative overflow-hidden bg-[#0a0a0a] text-[#F7F3EC]">
    {{-- Keep background footer.jpg — luxury near-black blend --}}
    <div class="absolute inset-0" aria-hidden="true">
        <img src="/assets/images/footer.jpg" alt="" class="h-full w-full object-cover object-center" loading="lazy">
        <div class="absolute inset-0 bg-[#0a0a0a]/70"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/35 to-transparent"></div>
    </div>
    <div class="relative mx-auto max-w-[1240px] px-6 py-16 lg:py-20">
        {{-- Centered brand — logo on every page (identical to homepage) --}}
        <div class="text-center">
            <img src="{{ asset('images/logo-white.png') }}" alt="Adventure Specialist Travel" class="mx-auto h-[3.2rem] w-auto object-contain opacity-95 [filter:drop-shadow(0_0_20px_rgba(255,255,255,0.12))] sm:h-[4rem] lg:h-[4.75rem]">
            <p class="sr-only">Adventure Specialist — Himalayan Journeys · Culture · Adventure · Wilderness</p>
        </div>

        {{-- Clean 3-column layout — gold headings bigger, white links bigger bold --}}
        <div class="mt-14 grid gap-10 border-t border-white/15 pt-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-12">
            {{-- Services --}}
            <nav aria-label="Footer services">
                <p class="text-sm font-black uppercase tracking-[0.2em] text-[#C9A86A]">Services</p>
                <ul class="mt-5 space-y-3 text-base font-semibold">
                    @forelse ($navServices as $service)
                        <li><a href="{{ $service->getPath() }}" class="text-white transition-colors hover:text-[#C9A86A]">{{ $service->title }}</a></li>
                    @empty
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Mountain Flight</a></li>
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Short Hiking</a></li>
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Jungle Safari</a></li>
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Paragliding</a></li>
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Bungee Jumping</a></li>
                        <li><a href="/ast-services/" class="text-white transition-colors hover:text-[#C9A86A]">Rafting</a></li>
                    @endforelse
                </ul>
            </nav>

            {{-- Destinations --}}
            <nav aria-label="Footer destinations">
                <p class="text-sm font-black uppercase tracking-[0.2em] text-[#C9A86A]">Destinations</p>
                <ul class="mt-5 space-y-3 text-base font-semibold">
                    @forelse ($navDestinations as $destination)
                        <li><a href="{{ $destination->getPath() }}" class="text-white transition-colors hover:text-[#C9A86A]">{{ $destination->title }}</a></li>
                    @empty
                        <li><a href="/destination/" class="text-white transition-colors hover:text-[#C9A86A]">Myanmar</a></li>
                        <li><a href="/destination/" class="text-white transition-colors hover:text-[#C9A86A]">Sikkim</a></li>
                        <li><a href="/destination/" class="text-white transition-colors hover:text-[#C9A86A]">Bhutan</a></li>
                        <li><a href="/destination/" class="text-white transition-colors hover:text-[#C9A86A]">Tibet</a></li>
                    @endforelse
                    @if ($navNepal)
                        <li><a href="{{ $navNepal->getPath() }}" class="text-white transition-colors hover:text-[#C9A86A]">{{ $navNepal->title }}</a></li>
                    @endif
                </ul>
            </nav>

            {{-- Contact --}}
            <div>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-[#C9A86A]">Contact Us</p>
                <address class="mt-5 space-y-3 text-base font-medium not-italic leading-relaxed [overflow-wrap:anywhere]">
                    <p class="font-black tracking-tight text-white">ADVENTURE SPECIALIST TRAVEL</p>
                    <p class="font-semibold text-white/80">Bungamati, Lalitpur, Nepal</p>
                    <p class="font-bold text-white">
                        <a href="tel:+97715173283" class="transition-colors hover:text-[#C9A86A]">+977 1 5173283</a><br>
                        <a href="tel:+9779851024546" class="transition-colors hover:text-[#C9A86A]">+977 9851024546</a> <span class="font-semibold text-white/60">— Raj K. Shrestha</span>
                    </p>
                    <p><a href="mailto:adventurespecialisttravel@gmail.com" class="font-semibold text-white/80 transition-colors hover:text-white">adventurespecialisttravel@gmail.com</a></p>
                    <p class="text-sm font-bold leading-relaxed text-white/50">Sun – Fri 9:00 – 16:00<br>Saturday – CLOSED</p>
                </address>
                {{-- Minimal gold line-style icons — captions below at mouse point --}}
                <div class="mt-6 flex items-center gap-3">
                    <a href="https://www.facebook.com/Adventure-Specialist-Travel-PvtLtd-318003508387072" target="_blank" rel="noopener" aria-label="Facebook"
                       class="group relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#C9A86A]/30 text-[#C9A86A] transition-colors hover:border-[#C9A86A] hover:bg-[#C9A86A] hover:text-[#0a0a0a]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M14 8h2.5L14 12h2.2l.4 3H14v7h-3v-7H9v-3h2V9.5A3.5 3.5 0 0 1 14.5 6H17V9h-2a1 1 0 0 0-1 1V12h3l-.5 3H14v7h-.001V8H14Z"/></svg>
                        <span class="pointer-events-none absolute left-1/2 top-full mt-2 -translate-x-1/2 whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-bold tracking-wide text-[#0a0a0a] opacity-0 shadow-lg transition-all duration-200 group-hover:opacity-100">Facebook</span>
                    </a>
                    <a href="mailto:adventurespecialisttravel@gmail.com" aria-label="Email"
                       class="group relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#C9A86A]/30 text-[#C9A86A] transition-colors hover:border-[#C9A86A] hover:bg-[#C9A86A] hover:text-[#0a0a0a]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7.5 12 13l9-5.5"/></svg>
                        <span class="pointer-events-none absolute left-1/2 top-full mt-2 -translate-x-1/2 whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-bold tracking-wide text-[#0a0a0a] opacity-0 shadow-lg transition-all duration-200 group-hover:opacity-100">Email</span>
                    </a>
                    <a href="tel:+9779851024546" aria-label="Phone"
                       class="group relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#C9A86A]/30 text-[#C9A86A] transition-colors hover:border-[#C9A86A] hover:bg-[#C9A86A] hover:text-[#0a0a0a]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6.5 3.5a1 1 0 0 0-1 1.2C6.1 10.1 10.1 14.1 15.3 15.5a1 1 0 0 0 1.2-1l-1.1-3.2a1 1 0 0 0-.6-.6l-2.8-1a1 1 0 0 0-1 .2l-1.4 1.4a12.2 12.2 0 0 1-3.8-3.8l1.4-1.4a1 1 0 0 0 .2-1l-1-2.8a1 1 0 0 0-.6-.6L6.5 3.5Z"/></svg>
                        <span class="pointer-events-none absolute left-1/2 top-full mt-2 -translate-x-1/2 whitespace-nowrap rounded-full bg-white px-3 py-1 text-xs font-bold tracking-wide text-[#0a0a0a] opacity-0 shadow-lg transition-all duration-200 group-hover:opacity-100">Phone</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Thin subtle divider above copyright — bigger muted text --}}
        <div class="mt-12 flex flex-col gap-3 border-t border-white/15 pt-6 text-sm font-semibold tracking-wide text-white/60 sm:flex-row sm:items-center sm:justify-between">
            <p>© Adventure Specialist Travel Pvt. Ltd. 2017–2025. All rights reserved.</p>
            <p>Made with <a href="https://megasoft.net.np/" target="_blank" rel="noopener" class="font-black text-white/80 transition-colors hover:text-white">Megasoft</a></p>
        </div>
    </div>
</footer>
