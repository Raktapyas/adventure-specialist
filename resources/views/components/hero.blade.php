@props(['eyebrow' => null, 'title' => null, 'lede' => null, 'image' => null, 'slides' => null])

@if (! empty($slides))
    {{-- Cinematic hero slider (homepage) — Revolution Slider-style treatment.
         Slides are server-rendered (SEO-friendly content), Alpine drives
         autoplay, crossfade, staggered text, progress, parallax. --}}
    <section
        x-data="{
            slides: {{ Js::from($slides) }},
            index: 0,
            ready: false,
            visible: true,
            onScreen: true,
            reducedMotion: false,
            progress: 0,
            startedAt: 0,
            rafId: null,
            touchStartX: 0,
            parallax: 0,
            duration: 7000,
            _raf: null,
            _io: null,
            _visHandler: null,

            init() {
                this.splitTitles();
                this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                this._visHandler = () => this.onVisibility();
                document.addEventListener('visibilitychange', this._visHandler);
                if (!this.reducedMotion) {
                    this.startedAt = performance.now();
                    this.rafId = requestAnimationFrame((t) => this.tick(t));
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => { this.onScreen = entry.isIntersecting; });
                    }, { threshold: 0.15 });
                    io.observe(this.$el);
                    this._io = io;
                }
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        this.ready = true;
                    });
                });
            },

            destroy() {
                if (this.rafId) cancelAnimationFrame(this.rafId);
                if (this._io) this._io.disconnect();
                document.removeEventListener('visibilitychange', this._visHandler);
            },

            tick(now) {
                if (this.slides.length < 2 || this.reducedMotion || !this.onScreen || !this.visible) {
                    this.startedAt = now;
                    this.rafId = requestAnimationFrame((t) => this.tick(t));
                    return;
                }
                const elapsed = now - this.startedAt;
                const t = Math.min(elapsed / this.duration, 1);
                this.progress = t;
                if (t >= 1) {
                    this.go(this.index + 1);
                    this.startedAt = now;
                    this.progress = 0;
                }
                this.rafId = requestAnimationFrame((t2) => this.tick(t2));
            },

            go(i) {
                const count = this.slides.length;
                if (count < 2) return;
                this.index = ((i % count) + count) % count;
                this.startedAt = performance.now();
                this.progress = 0;
            },

            next() { this.go(this.index + 1); },
            prev() { this.go(this.index - 1); },

            onVisibility() {
                this.visible = !document.hidden;
            },

            onScroll() {
                if (this._raf) return;
                this._raf = requestAnimationFrame(() => {
                    this._raf = null;
                    this.parallax = Math.min(window.scrollY * 0.12, 90);
                });
            },

            parallaxStyle(i) {
                if (this.reducedMotion || this.index !== i) return '';
                return 'transform: translate3d(0, ' + this.parallax + 'px, 0)';
            },

            swipeStart(e) {
                this.touchStartX = e.changedTouches[0].clientX;
            },

            swipeEnd(e) {
                const dx = e.changedTouches[0].clientX - this.touchStartX;
                if (Math.abs(dx) > 48) {
                    dx < 0 ? this.next() : this.prev();
                }
            },

            splitTitles() {
                this.$el.querySelectorAll('[data-hero-title]').forEach((node) => {
                    const words = node.textContent.trim().split(/\s+/);
                    if (words.length < 2) return;
                    node.textContent = '';
                    const frag = document.createDocumentFragment();
                    words.forEach((word, i) => {
                        const span = document.createElement('span');
                        span.className = 'hero-word';
                        span.style.setProperty('--i', i);
                        span.textContent = word;
                        frag.appendChild(span);
                        if (i < words.length - 1) frag.appendChild(document.createTextNode(' '));
                    });
                    node.appendChild(frag);
                });
            },
        }"
        @scroll.window="onScroll()"
        @touchstart.passive="swipeStart($event)"
        @touchend.passive="swipeEnd($event)"
        class="relative h-svh min-h-[560px] overflow-hidden bg-pine-deep text-paper lg:min-h-[620px]"
        role="region"
        aria-roledescription="carousel"
        aria-label="Adventure Specialist highlights"
        aria-live="off"
    >
        {{-- Slides --}}
        @foreach ($slides as $i => $slide)
            <div
                class="hero-slide {{ $i === 0 ? 'is-active' : '' }}"
                :class="index === {{ $i }} ? 'is-active' : ''"
                role="group"
                aria-roledescription="slide"
                aria-label="{{ __('Slide :number of :count', ['number' => $i + 1, 'count' => count($slides)]) }}"
                aria-hidden="{{ $i === 0 ? 'false' : 'true' }}"
                :aria-hidden="(index === {{ $i }}).toString()"
            >
                {{-- Layered background: Ken Burns media + scrims + vignette --}}
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute inset-0 will-change-transform {{ $slide['kenburns'] ?? 'animate-hero-zoom-in' }}" :style="parallaxStyle({{ $i }})">
                        @if (($slide['type'] ?? 'image') === 'video')
                            <video
                                src="{{ $slide['image'] }}"
                                class="h-full w-full object-cover brightness-[1.08] saturate-[0.95]"
                                autoplay muted loop playsinline preload="auto"
                                referrerpolicy="no-referrer"
                            ></video>
                        @else
                            <img
                                src="{{ $slide['image'] }}"
                                alt=""
                                class="hero-img h-full w-full object-cover brightness-[1.08] saturate-[0.95]"
                                loading="eager"
                                referrerpolicy="no-referrer"
                            >
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/95 via-pine-deep/30 to-pine-deep/10"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-pine-deep/60 via-pine-deep/15 to-transparent"></div>
                    <div class="absolute inset-0" style="background: radial-gradient(120% 90% at 50% 40%, transparent 55%, rgba(13, 18, 14, 0.5) 100%);"></div>
                </div>

                {{-- Slide content --}}
                <div
                    class="relative mx-auto flex h-full w-full max-w-[1240px] items-end px-6 pb-28 pt-28 sm:items-center sm:pt-36 lg:pb-24"
                    :class="ready && index === {{ $i }} ? 'hero-text-in' : ''"
                >
                    <div class="max-w-2xl lg:max-w-3xl">
                        @if (! empty($slide['eyebrow']))
                            <p class="hero-eyebrow flex items-center gap-3 text-xs font-bold uppercase tracking-[0.22em] text-royal-bright sm:text-[0.8125rem]">
                                <span class="h-px w-8 bg-royal-bright/70 sm:w-10" aria-hidden="true"></span>
                                {{ $slide['eyebrow'] }}
                            </p>
                        @endif

                        <h1 class="display mt-5 text-[2.5rem] leading-[1.05] text-paper sm:text-5xl lg:text-[3.75rem]" data-hero-title>
                            {{ $slide['title'] }}
                        </h1>

                        @if (! empty($slide['lede']))
                            <p class="hero-lede mt-6 max-w-2xl text-base leading-relaxed text-paper/85 sm:text-lg">
                                {{ $slide['lede'] }}
                            </p>
                        @endif

                        @if (! empty($slide['ctas']))
                            <div class="hero-cta mt-8 flex flex-wrap gap-3">
                                @foreach ($slide['ctas'] as $cta)
                                    <a href="{{ $cta['href'] }}" class="btn {{ ($cta['style'] ?? 'royal') === 'outline' ? 'btn-outline' : 'btn-royal' }}">
                                        {{ $cta['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Progress bars --}}
        <div class="absolute inset-x-0 bottom-0 z-10 flex justify-center px-6 pb-8">
            <div class="flex items-center gap-2" role="group" aria-label="Choose slide">
                <template x-for="(slide, i) in slides" :key="i">
                    <button
                        type="button"
                        class="relative h-1 w-9 overflow-hidden rounded-full bg-paper/25 transition-colors hover:bg-paper/40 sm:w-12 lg:w-16"
                        :aria-label="'Go to slide ' + (i + 1)"
                        :aria-current="index === i ? 'true' : 'false'"
                        @click="go(i)"
                    >
                        <span
                            class="hero-progress-fill absolute inset-0 rounded-full bg-paper"
                            :class="index === i ? 'opacity-100' : 'opacity-0'"
                            :style="index === i ? 'transform: scaleX(' + progress + ')' : 'transform: scaleX(0)'"
                        ></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Prev / next (desktop) --}}
        <div class="absolute top-1/2 left-6 z-10 hidden -translate-y-1/2 lg:block">
            <button type="button" class="hero-arrow" aria-label="Previous slide" @click="prev()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            </button>
        </div>
        <div class="absolute top-1/2 right-6 z-10 hidden -translate-y-1/2 lg:block">
            <button type="button" class="hero-arrow" aria-label="Next slide" @click="next()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </button>
        </div>
    </section>
@else
    <section class="relative flex h-[360px] items-center overflow-hidden bg-pine-deep pt-24 pb-10 text-paper sm:h-[420px] lg:h-[480px]">
        @if ($image)
            <div class="absolute inset-0">
                <img src="{{ $image }}" alt="" class="h-full w-full animate-slow-zoom object-cover object-center brightness-[1.15] saturate-[0.95]" loading="eager" referrerpolicy="no-referrer">
                {{-- Lighter overlay: imagery visible, bottom-weighted scrim keeps text readable --}}
                <div class="absolute inset-0 bg-gradient-to-t from-pine-deep via-pine-deep/20 to-pine-deep/5"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-pine-deep/30 via-pine-deep/10 to-transparent"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-pine-deep via-pine to-moss"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-pine-deep/80 to-transparent"></div>
        @endif

        <div class="relative mx-auto w-full max-w-[1240px] px-6">
            <div class="max-w-3xl">
                @if ($eyebrow)
                    <p class="animate-fade-up text-xs font-bold uppercase tracking-[0.12em] text-royal-bright sm:text-[0.8125rem] sm:tracking-[0.22em]" style="animation-delay: 120ms">
                        {{ $eyebrow }}
                    </p>
                @endif

                <h1 class="display mt-5 text-4xl leading-[1.05] text-paper sm:text-5xl lg:text-6xl split-reveal">
                    {{ $title }}
                </h1>

                @if ($lede)
                    <p class="mt-6 max-w-2xl animate-fade-up text-lg leading-relaxed text-paper/85" style="animation-delay: 260ms">
                        {{ $lede }}
                    </p>
                @endif

                @isset($slot)
                    <div class="mt-8 animate-fade-up" style="animation-delay: 380ms">
                        {{ $slot }}
                    </div>
                @endisset
            </div>
        </div>
    </section>
@endif
