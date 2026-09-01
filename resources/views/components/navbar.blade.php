@props(['active' => null])

<header x-data="{
        open: false,
        scrolled: false,
        about: false,
        trekkingOpen: false,
        destination: false,
        nepal: false,
        topOpen: null,
        navOpen: null,
        flips: {},
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
                @if(isset($navItems) && $navItems->isNotEmpty())
                    {{-- Dynamic navigation from Filament → Navigation Menu --}}
                    @foreach($navItems as $item)
                        @if($item->children->isNotEmpty())
                            <div class="relative group flyout-parent" x-init="$nextTick(()=>{const r=$el.querySelector('.flyout')?.getBoundingClientRect(); if(r && r.right>window.innerWidth-16) flips[{{ $item->id }}]=true})" @mouseenter="navOpen = {{ $item->id }}" @mouseleave="navOpen = null" @focusin="navOpen = {{ $item->id }}" @focusout="navOpen = null">
                                @php $isDropdown = $item->isDropdown(); @endphp
                                <a href="{{ $item->resolvedUrl() }}" @if(!$isDropdown && $item->open_in_new_tab) target="_blank" rel="noopener" @endif class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="(navOpen === {{ $item->id }}).toString()" @if($isDropdown) @click.prevent="navOpen = navOpen === {{ $item->id }} ? null : {{ $item->id }}" @endif>
                                    {{ $item->label }}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="navOpen === {{ $item->id }} ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                                </a>
                                <div role="menu" class="absolute top-full z-50 mt-0 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block" :class="[navOpen === {{ $item->id }} ? '!block' : '', flips[{{ $item->id }}] ? 'right-0' : 'left-0']">
                                    <x-navbar-flyout :items="$item->children" :level="0" />
                                </div>
                            </div>
                        @else
                            <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="nav-link">{{ $item->label }}</a>
                        @endif
                    @endforeach
                @else
                    {{-- Fallback (no navigation_items seeded yet) — legacy hard-coded behavior --}}
                    <a href="/" class="nav-link" :class="activeHome ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Home</a>

                    @php $aboutHasChildren = $navAboutPages->isNotEmpty(); @endphp
                    @if($aboutHasChildren)
                        <div class="relative group flyout-parent" @mouseenter="about = true" @mouseleave="about = false" @focusin="about = true" @focusout="about = false">
                            <a href="javascript:void(0)" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="about.toString()" :class="activePages ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''" @click.prevent="about = !about">
                                About Us
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="about ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                            </a>
                            <div role="menu" class="absolute left-0 top-full z-50 mt-0 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block" :class="about ? '!block' : ''">
                                <a href="/about-us/" class="flex items-center justify-between border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors hover:bg-accent hover:text-white">About Us</a>
                                @foreach ($navAboutPages as $page)
                                    @php $pageHasChildren = $page->children && $page->children->isNotEmpty(); @endphp
                                    <div class="flyout-item-wrap relative">
                                        <a href="{{ $page->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                            <span>{{ $page->title }}</span>
                                            @if($pageHasChildren)
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </a>
                                        @if($pageHasChildren)
                                            <div class="absolute left-full top-0 z-50 hidden w-[235px] flyout flyout-sub ml-1">
                                                @foreach($page->children as $grand)
                                                    <a href="{{ $grand->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $grand->title }}</a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="/about-us/" class="nav-link" :class="activePages ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">About Us</a>
                    @endif

                    @foreach ($navTopLevelPages as $section)
                        <div class="relative group flyout-parent" @mouseenter="topOpen = {{ $section->id }}" @mouseleave="topOpen = null" @focusin="topOpen = {{ $section->id }}" @focusout="topOpen = null">
                            <a href="{{ $section->getPath() }}" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="(topOpen === {{ $section->id }}).toString()" :class="activePages ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                                {{ $section->title }}
                                @if ($section->children->isNotEmpty())
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="topOpen === {{ $section->id }} ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                                @endif
                            </a>
                            @if ($section->children->isNotEmpty())
                                <div role="menu" class="absolute left-0 top-full z-50 mt-2 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block">
                                    @foreach ($section->children as $child)
                                        <div class="flyout-item-wrap relative">
                                            <a href="{{ $child->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                                <span>{{ $child->title }}</span>
                                                @if($child->children->isNotEmpty())
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </a>
                                            @if($child->children->isNotEmpty())
                                                <div class="absolute left-full top-0 z-50 hidden w-[235px] flyout flyout-sub ml-1">
                                                    @foreach($child->children as $grand)
                                                        <a href="{{ $grand->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $grand->title }}</a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @php $trekkingHasChildren = $navServices->isNotEmpty(); @endphp
                    @if($trekkingHasChildren)
                    <div class="relative group flyout-parent" @mouseenter="trekkingOpen = true" @mouseleave="trekkingOpen = false" @focusin="trekkingOpen = true" @focusout="trekkingOpen = false">
                        <a href="javascript:void(0)" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="trekkingOpen.toString()" :class="activeServices ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''" @click.prevent="trekkingOpen = !trekkingOpen">
                            Trekking &amp; Activities
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="trekkingOpen ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                        </a>
                        <div role="menu" class="absolute left-0 top-full z-50 mt-0 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block" :class="trekkingOpen ? '!block' : ''">
                            @foreach($navServices as $service)
                                <div class="flyout-item-wrap relative">
                                    <a href="{{ $service->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                        <span>{{ $service->title }}</span>
                                        @if($service->children->isNotEmpty())
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                    @if($service->children->isNotEmpty())
                                        <div class="absolute left-full top-0 z-50 hidden w-[235px] flyout flyout-sub ml-1">
                                            @foreach($service->children as $child)
                                                <div class="flyout-item-wrap relative">
                                                    <a href="{{ $child->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                                        <span>{{ $child->title }}</span>
                                                        @if($child->children->isNotEmpty())
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                                        @endif
                                                    </a>
                                                    @if($child->children->isNotEmpty())
                                                        <div class="absolute left-full top-0 z-50 hidden w-[235px] flyout flyout-sub ml-1">
                                                            @foreach($child->children as $grand)
                                                                <a href="{{ $grand->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $grand->title }}</a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                        <a href="/ast-services/" class="nav-link" :class="activeServices ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Trekking &amp; Activities</a>
                    @endif

                    @php $destHasChildren = $navDestinations->isNotEmpty(); @endphp
                    @if($destHasChildren)
                        <div class="relative group flyout-parent" @mouseenter="destination = true" @mouseleave="destination = false" @focusin="destination = true" @focusout="destination = false">
                            <a href="javascript:void(0)" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="destination.toString()" :class="activeDestinations ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''" @click.prevent="destination = !destination">
                                Destinations
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="destination ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                            </a>
                            <div role="menu" class="absolute right-0 top-full z-50 mt-0 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block" :class="destination ? '!block' : ''">
                                @foreach($navDestinations as $destination)
                                    <div class="flyout-item-wrap relative">
                                        <a href="{{ $destination->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                            <span>{{ $destination->title }}</span>
                                            @if($destination->children->isNotEmpty())
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </a>
                                        @if($destination->children->isNotEmpty())
                                            <div class="absolute right-full top-0 z-50 hidden w-[235px] flyout flyout-sub mr-1">
                                                @foreach($destination->children as $child)
                                                    @php $childHasGrand = $child->children && $child->children->isNotEmpty(); @endphp
                                                    @if($childHasGrand)
                                                        <div class="flyout-item-wrap relative">
                                                            <a href="{{ $child->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                                                <span>{{ $child->title }}</span>
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                                            </a>
                                                            <div class="absolute right-full top-0 z-50 hidden w-[235px] flyout flyout-sub mr-1">
                                                                @foreach($child->children as $grand)
                                                                    <a href="{{ $grand->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $grand->title }}</a>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a href="{{ $child->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $child->title }}</a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="/destination/" class="nav-link" :class="activeDestinations ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Destinations</a>
                    @endif

                    @if ($navNepal)
                        <div class="relative group flyout-parent" @mouseenter="nepal = true" @mouseleave="nepal = false" @focusin="nepal = true" @focusout="nepal = false">
                            <a href="{{ $navNepal->getPath() }}" class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="nepal.toString()" :class="activeNepal ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">
                                {{ $navNepal->title }}
                                @if ($navNepal->children->isNotEmpty())
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 transition-transform" :class="nepal ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                                @endif
                            </a>
                            @if ($navNepal->children->isNotEmpty())
                                <div role="menu" class="absolute right-0 top-full z-50 mt-2 hidden w-[235px] flyout flyout--has-children group-hover:block group-focus-within:block">
                                    @foreach($navNepal->children as $child)
                                        <div class="flyout-item-wrap relative">
                                            <a href="{{ $child->getPath() }}" class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">
                                                <span>{{ $child->title }}</span>
                                                @if($child->children && $child->children->isNotEmpty())
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </a>
                                            @if($child->children && $child->children->isNotEmpty())
                                                <div class="absolute right-full top-0 z-50 hidden w-[235px] flyout flyout-sub mr-1">
                                                    @foreach($child->children as $grand)
                                                        <a href="{{ $grand->getPath() }}" class="flex items-center border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white">{{ $grand->title }}</a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <a href="/contact/" class="nav-link" :class="activeContact ? (scrolled ? 'text-accent' : 'text-accent-bright') : ''">Contact</a>
                @endif

                {{-- Search — smooth dropdown below magnifier, :focus-within glass, orange accent, no push --}}
                <div class="relative group">
                    <button
                        type="button"
                        aria-label="Search"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20"
                        :class="scrolled ? 'text-ink-soft hover:text-accent hover:border-line' : 'text-paper/90 hover:text-accent-bright hover:border-white/20'"
                        onclick="this.nextElementSibling.querySelector('input').focus()"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    </button>
                    <div class="absolute right-0 top-full mt-3 w-[22rem] max-w-[90vw] rounded-card border border-white/20 bg-white/10 backdrop-blur-xl shadow-[0_8px_32px_rgba(0,0,0,0.18)] opacity-0 -translate-y-2 pointer-events-none group-focus-within:opacity-100 group-focus-within:translate-y-0 group-focus-within:pointer-events-auto transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] z-50">
                        <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2 p-2">
                            <input
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="Search treks, permits, best time..."
                                aria-label="Search treks and destinations"
                                autocomplete="off"
                                class="w-full rounded-full border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/60 caret-[var(--color-accent)] focus:border-[var(--color-accent)] focus:bg-white/20 focus:text-white focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20 transition-colors"
                            />
                            <button type="submit" class="shrink-0 rounded-full bg-[var(--color-accent)] px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-[var(--color-accent-dark)] transition-colors">Go</button>
                        </form>
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
            @if(isset($navItems) && $navItems->isNotEmpty())
                @foreach($navItems as $item)
                    @if($item->children->isNotEmpty())
                        <div x-data="{open:false}" class="border-b border-line/10">
                            <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                                <span>{{ $item->label }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="pb-1">
                                @if(!$item->isDropdown())
                                    <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold {{ ($navFlyoutStyle ?? 'image') === 'image' ? 'text-[#F97316] hover:text-[#F97316]' : 'text-accent hover:text-accent' }}">View all {{ $item->label }} →</a>
                                @endif
                                <x-navbar-mobile-flyout :items="$item->children" :level="0" :image="(($navFlyoutStyle ?? 'image') === 'image')" />
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="block rounded px-3 py-2.5 text-base text-ink {{ ($navFlyoutStyle ?? 'image') === 'image' ? 'hover:bg-[#F97316] hover:text-white' : 'hover:bg-paper-soft hover:text-accent' }}">{{ $item->label }}</a>
                    @endif
                @endforeach
            @else
                {{-- Fallback mobile - 3-level vertical cascading accordion (only dropdowns when children/grandchildren exist) --}}
                <a href="/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Home</a>
                @php $mobileAboutHasChildren = $navAboutPages->isNotEmpty(); @endphp
                @if($mobileAboutHasChildren)
                    <div x-data="{open:false}" class="border-b border-line/10">
                        <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                            <span>About Us</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="pb-1">
                            <a href="/about-us/" class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold text-accent hover:text-accent">View all About Us →</a>
                            @foreach ($navAboutPages as $page)
                                @php $hasGrand = $page->children && $page->children->isNotEmpty(); @endphp
                                @if($hasGrand)
                                    <div x-data="{open:false}" class="border-b border-line/20">
                                        <button @click="open=!open" class="flex w-full items-center justify-between rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">
                                            <span>{{ $page->title }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <div x-show="open" x-collapse class="pb-1">
                                            <a href="{{ $page->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $page->title }}</a>
                                            @foreach ($page->children as $grand)
                                                <a href="{{ $grand->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">{{ $grand->title }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $page->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $page->title }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="/about-us/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">About Us</a>
                @endif
                @foreach ($navTopLevelPages as $section)
                    @php $sectionHasChildren = $section->children->isNotEmpty(); @endphp
                    @if($sectionHasChildren)
                        <div x-data="{open:false}" class="border-b border-line/10">
                            <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                                <span>{{ $section->title }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="pb-1">
                                <a href="{{ $section->getPath() }}" class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold text-accent hover:text-accent">View {{ $section->title }} →</a>
                                @foreach ($section->children as $child)
                                    @php $childHasGrand = $child->children->isNotEmpty(); @endphp
                                    @if($childHasGrand)
                                        <div x-data="{open:false}" class="border-b border-line/20">
                                            <button @click="open=!open" class="flex w-full items-center justify-between rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">
                                                <span>{{ $child->title }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <div x-show="open" x-collapse class="pb-1">
                                                <a href="{{ $child->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $child->title }}</a>
                                                @foreach ($child->children as $grand)
                                                    <a href="{{ $grand->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">{{ $grand->title }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ $child->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $child->title }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $section->getPath() }}" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">{{ $section->title }}</a>
                    @endif
                @endforeach
                @php $servicesHasChildren = $navServices->isNotEmpty(); @endphp
                @if($servicesHasChildren)
                    <div x-data="{open:false}" class="border-b border-line/10">
                        <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                            <span>Trekking &amp; Activities</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="pb-1">
                            <a href="/ast-services/" class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold text-accent hover:text-accent">View all Trekking &amp; Activities →</a>
                            @foreach ($navServices as $service)
                                @php $svcHasChildren = $service->children->isNotEmpty(); @endphp
                                @if($svcHasChildren)
                                    <div x-data="{open:false}" class="border-b border-line/20">
                                        <button @click="open=!open" class="flex w-full items-center justify-between rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">
                                            <span>{{ $service->title }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <div x-show="open" x-collapse class="pb-1">
                                            <a href="{{ $service->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $service->title }}</a>
                                            @foreach ($service->children as $child)
                                                @php $childHasGrand = $child->children->isNotEmpty(); @endphp
                                                @if($childHasGrand)
                                                    <div x-data="{open:false}" class="border-b border-line/20">
                                                        <button @click="open=!open" class="flex w-full items-center justify-between rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">
                                                            <span>{{ $child->title }}</span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                                        </button>
                                                        <div x-show="open" x-collapse class="pb-1">
                                                            <a href="{{ $child->getPath() }}" class="block rounded py-1.5 pl-12 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $child->title }}</a>
                                                            @foreach ($child->children as $grand)
                                                                <a href="{{ $grand->getPath() }}" class="block rounded py-1.5 pl-12 pr-3 text-xs text-ink-faint hover:text-accent">{{ $grand->title }}</a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <a href="{{ $child->getPath() }}" class="block rounded py-2 pl-10 pr-3 text-xs text-ink-faint hover:bg-paper-soft hover:text-accent">{{ $child->title }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $service->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $service->title }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="/ast-services/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Trekking &amp; Activities</a>
                @endif
                @php $destMobileHasChildren = $navDestinations->isNotEmpty(); @endphp
                @if($destMobileHasChildren)
                    <div x-data="{open:false}" class="border-b border-line/10">
                        <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                            <span>Destinations</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="pb-1">
                            <a href="/destination/" class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold text-accent hover:text-accent">View all Destinations →</a>
                            @foreach ($navDestinations as $destination)
                                @php $destHasChildren = $destination->children->isNotEmpty(); @endphp
                                @if($destHasChildren)
                                    <div x-data="{open:false}" class="border-b border-line/20">
                                        <button @click="open=!open" class="flex w-full items-center justify-between rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">
                                            <span>{{ $destination->title }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <div x-show="open" x-collapse class="pb-1">
                                            <a href="{{ $destination->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $destination->title }}</a>
                                            @foreach ($destination->children as $child)
                                                @php $childHasGrand = $child->children && $child->children->isNotEmpty(); @endphp
                                                @if($childHasGrand)
                                                    <div x-data="{open:false}" class="border-b border-line/20">
                                                        <button @click="open=!open" class="flex w-full items-center justify-between rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">
                                                            <span>{{ $child->title }}</span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                                        </button>
                                                        <div x-show="open" x-collapse class="pb-1">
                                                            @foreach ($child->children as $grand)
                                                                <a href="{{ $grand->getPath() }}" class="block rounded py-1.5 pl-12 pr-3 text-xs text-ink-faint hover:text-accent">{{ $grand->title }}</a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <a href="{{ $child->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">{{ $child->title }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $destination->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $destination->title }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="/destination/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Destinations</a>
                @endif
                @if ($navNepal)
                    @php $nepalMobileHasChildren = $navNepal->children->isNotEmpty(); @endphp
                    @if($nepalMobileHasChildren)
                        <div x-data="{open:false}" class="border-b border-line/10">
                            <button @click="open=!open" class="flex w-full items-center justify-between rounded px-3 py-2.5 text-base font-medium text-ink hover:bg-paper-soft hover:text-accent">
                                <span>{{ $navNepal->title }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="pb-1">
                                <a href="{{ $navNepal->getPath() }}" class="block rounded py-1.5 pl-7 pr-3 text-xs font-semibold text-accent hover:text-accent">View {{ $navNepal->title }} →</a>
                                @foreach ($navNepal->children as $child)
                                    @php $nepalChildHasGrand = $child->children && $child->children->isNotEmpty(); @endphp
                                    @if($nepalChildHasGrand)
                                        <div x-data="{open:false}" class="border-b border-line/20">
                                            <button @click="open=!open" class="flex w-full items-center justify-between rounded py-1.5 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">
                                                <span>{{ $child->title }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <div x-show="open" x-collapse class="pb-1">
                                                <a href="{{ $child->getPath() }}" class="block rounded py-1.5 pl-10 pr-3 text-xs text-ink-faint hover:text-accent">View {{ $child->title }}</a>
                                                @foreach ($child->children as $grand)
                                                    <a href="{{ $grand->getPath() }}" class="block rounded py-1.5 pl-12 pr-3 text-xs text-ink-faint hover:text-accent">{{ $grand->title }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ $child->getPath() }}" class="block rounded py-2 pl-7 pr-3 text-sm text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $child->title }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $navNepal->getPath() }}" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">{{ $navNepal->title }}</a>
                    @endif
                @endif
                <a href="/contact/" class="block rounded px-3 py-2.5 text-base text-ink hover:bg-paper-soft hover:text-accent">Contact</a>
            @endif
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
