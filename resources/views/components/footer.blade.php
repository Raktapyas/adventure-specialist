<footer class="mt-24 bg-pine-deep text-paper">
    <div class="mx-auto max-w-[1240px] px-6 py-16">
        <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
            {{-- Brand --}}
            <div>
                <p class="text-lg font-extrabold tracking-tight">Adventure Specialist Travel</p>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-paper/70">
                    Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar.
                </p>
                <div class="mt-6 flex items-center gap-4 text-xs tracking-wider text-paper/60">
                    <a href="https://www.facebook.com/Adventure-Specialist-Travel-PvtLtd-318003508387072" target="_blank" rel="noopener" class="hover:text-royal-bright">Facebook</a>
                </div>
            </div>

            {{-- AST Services --}}
            <nav aria-label="Footer services">
                <p class="eyebrow text-paper/50">AST Services</p>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse ($navServices as $service)
                        <li><a href="{{ $service->getPath() }}" class="text-paper/75 transition-colors hover:text-royal-bright">{{ $service->title }}</a></li>
                    @empty
                        <li class="text-paper/75">Mountain Flight</li>
                        <li class="text-paper/75">Short Hiking</li>
                        <li class="text-paper/75">Jungle Safari</li>
                        <li class="text-paper/75">Paragliding</li>
                        <li class="text-paper/75">Bungee Jumping</li>
                        <li class="text-paper/75">Rafting</li>
                    @endforelse
                </ul>
            </nav>

            {{-- Destinations --}}
            <nav aria-label="Footer destinations">
                <p class="eyebrow text-paper/50">Destination</p>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse ($navDestinations as $destination)
                        <li><a href="{{ $destination->getPath() }}" class="text-paper/75 transition-colors hover:text-royal-bright">{{ $destination->title }}</a></li>
                    @empty
                        <li class="text-paper/75">Myanmar</li>
                        <li class="text-paper/75">Sikkim</li>
                        <li class="text-paper/75">Bhutan</li>
                        <li class="text-paper/75">Tibet</li>
                    @endforelse
                    @if ($navNepal)
                        <li><a href="{{ $navNepal->getPath() }}" class="text-paper/75 transition-colors hover:text-royal-bright">{{ $navNepal->title }}</a></li>
                    @endif
                </ul>
            </nav>

            {{-- Contact --}}
            <div>
                <p class="eyebrow text-paper/50">Contact Info</p>
                <address class="mt-5 space-y-3 text-sm not-italic [overflow-wrap:anywhere]">
                    <p class="text-paper/75">ADVENTURE SPECIALIST TRAVEL</p>
                    <p class="text-paper/75">Bungamati, Lalitpur, Nepal</p>
                    <p class="text-paper/75">
                        <a href="tel:+97715173283" class="hover:text-royal-bright">+977 1 5173283</a>,<br>
                        <a href="tel:+9779851024546" class="hover:text-royal-bright">+977 9851024546 (M.D. Raj K. Shrestha)</a>
                    </p>
                    <p><a href="mailto:adventurespecialisttravel@gmail.com" class="hover:text-royal-bright">adventurespecialisttravel@gmail.com</a></p>
                    <p class="text-paper/75">Sun – Fri 9.00 – 16.00<br>Saturday – CLOSED</p>
                </address>
            </div>
        </div>

        <div class="mt-14 border-t border-paper/15 pt-6 flex flex-col gap-2 text-xs text-paper/50 sm:flex-row sm:items-center sm:justify-between">
            <p>Adventure Specialist Travel Pvt. Ltd. 2017-2025.</p>
            <p>Nepal · Bhutan · Sikkim · Tibet · Myanmar</p>
        </div>
    </div>
</footer>