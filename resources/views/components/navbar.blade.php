@props(['active' => null])

<header x-data="{
        open: false,
        scrolled: false,
        about: false,
        trekkingOpen: false,
        destination: false,
        nepal: false,
        topOpen: null,
        searchOpen: false,
        searchQuery: '',
        suggestions: [],
        loading: false,
        selected: -1,
        _suggestTimer: null,
        _suggestAbort: null,
        activeHome: @js(request()->routeIs('home')),
        activePages: @js(request()->routeIs('pages.*')),
        activeServices: @js(request()->routeIs('services.*')),
        activeDestinations: @js(request()->routeIs('destinations.index','destinations.show','destinations.show.nested','destinations.show.deep')),
        activeNepal: @js(request()->routeIs('destinations.nepal*')),
        activeContact: @js(request()->routeIs('contact.*')),
        onSearchInput() {
            clearTimeout(this._suggestTimer);
            if (this._suggestAbort) { try { this._suggestAbort.abort(); } catch(e) {} this._suggestAbort = null; }
            const q = this.searchQuery.trim();
            if (q.length < 2) { this.suggestions = []; this.selected = -1; this.loading = false; return; }
            this._suggestTimer = setTimeout(() => this.fetchSuggest(), 250);
        },
        async fetchSuggest() {
            const q = this.searchQuery.trim();
            if (q.length < 2) return;
            this.loading = true;
            if (this._suggestAbort) { try { this._suggestAbort.abort(); } catch(e) {} }
            this._suggestAbort = new AbortController();
            try {
                const res = await fetch('/search/suggest?q=' + encodeURIComponent(q), { signal: this._suggestAbort.signal, headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('bad');
                const data = await res.json();
                this.suggestions = Array.isArray(data) ? data : [];
                this.selected = -1;
            } catch(e) {
                if (e.name !== 'AbortError') this.suggestions = [];
            } finally {
                this.loading = false;
            }
        },
        onSearchKeydown(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!this.suggestions.length) return;
                this.selected = (this.selected + 1) % this.suggestions.length;
                this.$nextTick(() => { const el = document.getElementById('suggestion-' + this.selected); if (el) el.scrollIntoView({block:'nearest'}); });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!this.suggestions.length) return;
                this.selected = this.selected <= 0 ? this.suggestions.length - 1 : this.selected - 1;
                this.$nextTick(() => { const el = document.getElementById('suggestion-' + this.selected); if (el) el.scrollIntoView({block:'nearest'}); });
            } else if (e.key === 'Enter') {
                if (this.selected >= 0 && this.suggestions[this.selected]) {
                    e.preventDefault();
                    window.location.href = this.suggestions[this.selected].url;
                }
            } else if (e.key === 'Escape') {
                this.searchOpen = false;
                this.suggestions = [];
                this.selected = -1;
                this.$nextTick(() => { if (this.$refs.searchInput) this.$refs.searchInput.focus(); });
            }
        },
        highlight(text) {
            const q = this.searchQuery.trim();
            if (!q || q.length < 2) return this.escapeHtml(text);
            const esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            try {
                return this.escapeHtml(text).replace(new RegExp('(' + esc + ')', 'ig'), '<mark class=\'bg-accent-faint text-accent font-semibold rounded px-0.5\'>$1</mark>');
            } catch(e) { return this.escapeHtml(text); }
        },
        escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\x22/g,'&quot;').replace(/\x27/g,'&#039;'); }
    }"
    @scroll.window="scrolled = window.scrollY > 12"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
    :class="scrolled ? 'bg-paper/95 backdrop-blur-md shadow-nav' : 'bg-transparent'">

    {{-- Main navigation --}}
    <div class="mx-auto max-w-[1240px] px-6">
        <nav class="flex items-center justify-between py-5" aria-label="Primary">
            {{-- Brand: CMS-managed via Site Settings → Branding; stays left, always visible --}}
            <a href="/" class="flex items-center shrink-0" aria-label="Adventure Specialist Travel — Home">
                <img src="{{ asset($siteBranding['logo'] ?? '/images/logo.png') }}" alt="Adventure Specialist Travel" class="h-11 w-auto sm:h-12 drop-shadow-sm">
            </a>

            {{-- Desktop links --}}
            <div class="hidden items-center gap-7 lg:flex text-paper/90" :class="scrolled ? '!text-ink-soft' : ''">
                <a href="/" class="nav-link" :class="activeHome ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Home</a>

                {{-- About Us --}}
                <div class="relative group" @mouseenter="about = true" @mouseleave="about = false" @focusin="about = true" @focusout="about = false">
                    <a href="/about-us/" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="about.toString()" :class="activePages ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                        About Us
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="about ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div role="menu" class="absolute left-0 top-full hidden w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm group-hover:block group-focus-within:block">
                        <a href="/about-us/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">About Us</a>
                        @foreach ($navAboutPages as $page)
                            <a href="{{ $page->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $page->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Standalone top-level pages (managed in Filament > Pages) --}}
                @foreach ($navTopLevelPages as $section)
                    <div class="relative group" @mouseenter="topOpen = {{ $section->id }}" @mouseleave="topOpen = null" @focusin="topOpen = {{ $section->id }}" @focusout="topOpen = null">
                        <a href="{{ $section->getPath() }}" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="(topOpen === {{ $section->id }}).toString()" :class="activePages ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                            {{ $section->title }}
                            @if ($section->children->isNotEmpty())
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="topOpen === {{ $section->id }} ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                            @endif
                        </a>
                        @if ($section->children->isNotEmpty())
                            <div role="menu" class="absolute left-0 top-full hidden w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm group-hover:block group-focus-within:block">
                                <a href="{{ $section->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $section->title }}</a>
                                @foreach ($section->children as $child)
                                    <a href="{{ $child->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Trekking & Activities (Services) --}}
                <div class="relative group" @mouseenter="trekkingOpen = true" @mouseleave="trekkingOpen = false" @focusin="trekkingOpen = true" @focusout="trekkingOpen = false">
                    <a href="/ast-services/" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="trekkingOpen.toString()" :class="activeServices ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                        Trekking &amp; Activities
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="trekkingOpen ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div role="menu" class="absolute left-0 top-full hidden w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm group-hover:block group-focus-within:block">
                        <a href="/ast-services/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">All Trekking &amp; Activities</a>
                        @foreach ($navServices as $service)
                            <a href="{{ $service->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $service->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Destinations --}}
                <div class="relative group" @mouseenter="destination = true" @mouseleave="destination = false" @focusin="destination = true" @focusout="destination = false">
                    <a href="/destination/" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="destination.toString()" :class="activeDestinations ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                        Destinations
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="destination ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                    </a>
                    <div role="menu" class="absolute left-0 top-full hidden w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm group-hover:block group-focus-within:block">
                        <a href="/destination/" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">All Destinations</a>
                        @foreach ($navDestinations as $destination)
                            <a href="{{ $destination->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $destination->title }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Nepal (standalone) --}}
                @if ($navNepal)
                    <div class="relative group" @mouseenter="nepal = true" @mouseleave="nepal = false" @focusin="nepal = true" @focusout="nepal = false">
                        <a href="{{ $navNepal->getPath() }}" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="nepal.toString()" :class="activeNepal ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                            {{ $navNepal->title }}
                            @if ($navNepal->children->isNotEmpty())
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="nepal ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                            @endif
                        </a>
                        @if ($navNepal->children->isNotEmpty())
                            <div role="menu" class="absolute left-0 top-full hidden w-72 rounded-card border border-line bg-paper-soft/95 p-2 text-ink-soft shadow-card backdrop-blur-sm group-hover:block group-focus-within:block">
                                <a href="{{ $navNepal->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $navNepal->title }}</a>
                                @foreach ($navNepal->children as $child)
                                    <a href="{{ $child->getPath() }}" class="block rounded px-3 py-2 text-sm hover:bg-paper hover:text-accent">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <a href="/contact/" class="nav-link" :class="activeContact ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Contact</a>

                {{-- Search icon — desktop (inside hidden nav), keep clickable --}}
                <div class="relative" @click.outside="searchOpen = false; suggestions = []; selected = -1;">
                    <button type="button" @click="searchOpen = !searchOpen; if(searchOpen) $nextTick(() => $refs.searchInput && $refs.searchInput.focus())" :class="scrolled ? 'text-ink-soft hover:text-accent' : 'text-paper/90 hover:text-accent-bright'" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-transparent transition-colors hover:border-line focus:outline-none focus:ring-2 focus:ring-accent/20" aria-label="Search" :aria-expanded="searchOpen.toString()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    </button>
                    <div x-show="searchOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="absolute right-0 top-full mt-3 w-[28rem] max-w-[90vw] rounded-card border border-line bg-paper-soft p-1.5 shadow-card z-50">
                        <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2">
                            <input x-ref="searchInput" name="q" x-model="searchQuery" @input="onSearchInput()" @keydown="onSearchKeydown($event)" placeholder="Search treks, permits, best time..." class="w-full rounded bg-paper px-3 py-2 text-sm text-ink placeholder:text-ink-faint focus:outline-none focus:ring-2 focus:ring-accent/20" autocomplete="off" role="combobox" :aria-expanded="searchOpen.toString()" aria-haspopup="listbox" aria-controls="search-suggestions" aria-autocomplete="list" :aria-activedescendant="selected >= 0 ? 'suggestion-' + selected : null" />
                            <button type="submit" class="shrink-0 rounded bg-accent px-4 py-2 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-accent-dark">Go</button>
                        </form>
                        <div x-show="loading" x-cloak class="px-3 py-2 text-center text-xs text-ink-faint">Searching…</div>
                        <ul x-show="suggestions.length > 0" id="search-suggestions" role="listbox" class="mt-2 max-h-[60vh] overflow-y-auto divide-y divide-line/60">
                            <template x-for="(item, idx) in suggestions" :key="item.url">
                                <li role="option" :id="'suggestion-' + idx" :aria-selected="(selected === idx).toString()">
                                    <a :href="item.url" @mouseenter="selected = idx" :class="selected === idx ? 'bg-paper text-accent' : 'hover:bg-paper hover:text-accent'" class="flex flex-col gap-0.5 rounded px-3 py-2.5 text-sm transition-colors">
                                        <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.14em]" :class="selected === idx ? 'text-accent' : 'text-ink-faint'"><span x-text="item.type === 'Trekking & Activities' ? '⛰' : item.type === 'Destinations' ? '◎' : item.type === 'Packages' ? '◈' : '▤'"></span><span x-text="item.type"></span></span>
                                        <span class="font-semibold leading-tight" x-html="highlight(item.title)"></span>
                                        <span x-show="item.excerpt" class="line-clamp-1 text-xs text-ink-faint" x-text="item.excerpt"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <div x-show="searchQuery.trim().length >= 2 && !loading && suggestions.length === 0" x-cloak class="px-3 py-3 text-center text-sm text-ink-faint">No matches — press Enter to search all</div>
                        <a x-show="suggestions.length > 0" :href="'/search?q=' + encodeURIComponent(searchQuery)" class="mt-2 block rounded bg-paper px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-accent hover:bg-accent hover:text-white transition-colors">View all results →</a>
                    </div>
                </div>

                <a href="/contact/#enquiry" class="btn btn-royal px-5! py-2.5! text-xs uppercase tracking-wider">
                    Plan a Trip
                </a>
            </div>

            {{-- Mobile toggle --}}
            <button type="button" @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-card transition-colors lg:hidden"
                :class="scrolled ? 'border border-line text-ink' : 'border border-paper/40 text-paper'"
                :aria-expanded="open.toString()" aria-controls="mobile-menu" :aria-label="open ? 'Close menu' : 'Open menu'">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </nav>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak x-transition.opacity.duration.200ms id="mobile-menu"
        class="border-t border-line bg-paper lg:hidden max-h-[calc(100vh-4rem)] overflow-y-auto">
        <nav class="mx-auto max-w-[1240px] space-y-1 px-6 py-6" aria-label="Mobile">
            {{-- Mobile search with autocomplete --}}
            <div class="mb-4" @click.outside="if(!open) { suggestions = []; selected = -1; }">
                <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2">
                    <input name="q" type="search" x-model="searchQuery" @input="onSearchInput(); searchOpen = true" @keydown="onSearchKeydown($event)" placeholder="Search treks, permits..." role="combobox" :aria-expanded="searchOpen.toString()" aria-haspopup="listbox" aria-controls="search-suggestions-mobile" autocomplete="off" class="w-full rounded-card border border-line bg-paper-soft px-3 py-2.5 text-sm text-ink placeholder:text-ink-faint focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20" />
                    <button type="submit" class="rounded-card bg-accent px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white">Go</button>
                </form>
                <div x-show="searchOpen && suggestions.length > 0" x-cloak id="search-suggestions-mobile" class="mt-2 rounded-card border border-line bg-paper-soft p-2 shadow-card">
                    <ul role="listbox" class="divide-y divide-line/60">
                        <template x-for="(item, idx) in suggestions" :key="item.url">
                            <li role="option" :aria-selected="(selected === idx).toString()">
                                <a :href="item.url" @mouseenter="selected = idx" :class="selected === idx ? 'bg-paper text-accent' : 'hover:bg-paper hover:text-accent'" class="flex flex-col gap-0.5 rounded px-3 py-2.5 text-sm">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink-faint" x-text="item.type"></span>
                                    <span class="font-semibold" x-html="highlight(item.title)"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                    <a :href="'/search?q=' + encodeURIComponent(searchQuery)" class="mt-2 block rounded bg-paper px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-accent">View all →</a>
                </div>
            </div>
            <a href="/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Home</a>
            <a href="/about-us/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">About Us</a>
            @foreach ($navTopLevelPages as $section)
                <a href="{{ $section->getPath() }}" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">{{ $section->title }}</a>
                @foreach ($section->children as $child)
                    <a href="{{ $child->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $child->title }}</a>
                @endforeach
            @endforeach
            <a href="/ast-services/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Trekking &amp; Activities</a>
            <a href="/destination/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Destinations</a>
            @foreach ($navDestinations as $destination)
                <a href="{{ $destination->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $destination->title }}</a>
            @endforeach
            @if ($navNepal)
                <a href="{{ $navNepal->getPath() }}" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">{{ $navNepal->title }}</a>
                @foreach ($navNepal->children as $child)
                    <a href="{{ $child->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $child->title }}</a>
                @endforeach
            @endif
            <a href="/contact/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Contact</a>
            <div class="border-t border-line pt-4">
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-ink-faint">Contact</p>
                <a href="tel:+97715173283" class="mt-1 block rounded px-3 py-2 text-sm text-ink-soft hover:text-accent">+977 1 5173283</a>
                <a href="tel:+9779851024546" class="block rounded px-3 py-2 text-sm text-ink-soft hover:text-accent">+977 9851024546</a>
                <a href="mailto:adventurespecialisttravel@gmail.com" class="block rounded px-3 py-2 text-sm text-ink-soft hover:text-accent">adventurespecialisttravel@gmail.com</a>
            </div>
            <a href="/contact/#enquiry" class="btn btn-royal mt-2 w-full">Plan a Trip</a>
        </nav>
    </div>
</header>
