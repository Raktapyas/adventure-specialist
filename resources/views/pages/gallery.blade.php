@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <x-hero
        title="AST Photo Gallery"
        lede="Moments from the mountains, the jungles and the valleys we call home."
        image="/assets/images/cover-all.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        @if ($images->isNotEmpty())
            <div class="columns-1 gap-5 sm:columns-2 lg:columns-3" data-gallery>
                @foreach ($images as $image)
                    @php $isVideo = $image->media?->isVideo() ?? false; @endphp
                    <figure class="group mb-5 break-inside-avoid reveal cursor-zoom-in"
                            data-lb-trigger
                            data-src="{{ $image->image_url }}"
                            data-type="{{ $isVideo ? 'video' : 'image' }}"
                            data-caption="{{ $image->caption ?? '' }}">
                        <div class="block overflow-hidden rounded-card img-zoom">
                            @if ($isVideo)
                                {{-- Thumbnail-style preview: silent looping clip with a play cue --}}
                                <div class="relative">
                                    <x-media-file :src="$image->image_url" type="video"
                                                  class="w-full object-cover"/>
                                    <span class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-black/55 text-white ring-1 ring-white/40">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z"/></svg>
                                        </span>
                                    </span>
                                </div>
                            @else
                                <x-media-file :src="$image->image_url" :alt="$image->caption ?? 'Gallery image'"
                                              class="w-full object-cover"/>
                            @endif
                        </div>
                        @if ($image->caption)
                            <figcaption class="mt-3 text-sm text-ink-faint">{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @else
            <p class="text-ink-faint">Gallery is being updated — new Himalayan moments arriving soon. Please check back shortly or contact us for recent trip photos.</p>
        @endif
    </section>

    {{-- Full-screen viewer --}}
    <div id="lightbox" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-label="Image viewer">
        <div data-lb-backdrop class="absolute inset-0 bg-black/95 opacity-0 backdrop-blur-sm transition-opacity duration-300"></div>

        {{-- Expanding image frame --}}
        <img data-lb-frame src="" alt=""
             class="absolute rounded-lg shadow-[0_40px_120px_rgba(0,0,0,0.6)] will-change-transform"
             style="transition: none;" draggable="false">

        {{-- Video viewer (fades in centered; controls + sound allowed on tap) --}}
        <video data-lb-video src="" controls autoplay playsinline preload="metadata"
               class="absolute hidden rounded-lg shadow-[0_40px_120px_rgba(0,0,0,0.6)] opacity-0 transition-opacity duration-300"></video>

        <p data-lb-caption class="absolute bottom-7 left-1/2 max-w-[80vw] -translate-x-1/2 truncate text-center text-sm text-white/75 opacity-0 transition-opacity duration-300"></p>
        <p data-lb-counter class="absolute left-1/2 top-6 -translate-x-1/2 text-xs font-semibold tracking-[0.3em] text-white/50 opacity-0 transition-opacity duration-300"></p>

        <button type="button" data-lb-close aria-label="Close"
                class="absolute right-5 top-5 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white opacity-0 backdrop-blur-sm transition-all duration-300 hover:bg-white/25">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <button type="button" data-lb-prev aria-label="Previous image"
                class="absolute left-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white opacity-0 backdrop-blur-sm transition-all duration-300 hover:bg-white/25 md:left-8">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </button>

        <button type="button" data-lb-next aria-label="Next image"
                class="absolute right-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white opacity-0 backdrop-blur-sm transition-all duration-300 hover:bg-white/25 md:right-8">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
    </div>

    <script>
    (function () {
        const grid = document.querySelector('[data-gallery]');
        const box = document.getElementById('lightbox');
        if (!grid || !box) return;

        const triggers = [...grid.querySelectorAll('[data-lb-trigger]')];
        const backdrop = box.querySelector('[data-lb-backdrop]');
        const frame = box.querySelector('[data-lb-frame]');
        const video = box.querySelector('[data-lb-video]');
        const captionEl = box.querySelector('[data-lb-caption]');
        const counterEl = box.querySelector('[data-lb-counter]');
        const uiFade = [backdrop, captionEl, counterEl, ...box.querySelectorAll('button')];

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let index = 0;
        let open = false;
        let lastFocus = null;

        const targetRect = () => {
            const vw = window.innerWidth * 0.92;
            const vh = window.innerHeight * 0.84;
            const active = isVideo() ? video : frame;
            const ratio = (active.tagName === 'VIDEO')
                ? 16 / 9
                : ((frame.naturalWidth && frame.naturalHeight) ? frame.naturalWidth / frame.naturalHeight : 3 / 2);
            let w = vw, h = vw / ratio;
            if (h > vh) { h = vh; w = vh * ratio; }
            return {
                left: (window.innerWidth - w) / 2,
                top: (window.innerHeight - h) / 2,
                width: w,
                height: h,
            };
        };

        const syncFrame = (rect) => {
            const el = isVideo() ? video : frame;
            el.style.left = rect.left + 'px';
            el.style.top = rect.top + 'px';
            el.style.width = rect.width + 'px';
            el.style.height = rect.height + 'px';
        };

        const fadeUi = (show) => {
            uiFade.forEach((el) => {
                el.style.opacity = show ? '' : '0';
                el.style.pointerEvents = show ? '' : 'none';
            });
        };

        const isVideo = () => triggers[index]?.dataset.type === 'video';

        const showOnly = (el) => {
            [frame, video].forEach((node) => {
                const active = node === el;
                node.classList.toggle('hidden', !active);
                node.style.opacity = active ? '' : '0';
            });
        };

        const stopVideo = () => {
            video.pause();
            video.removeAttribute('src');
            video.load();
        };

        const load = (i) => {
            index = (i + triggers.length) % triggers.length;
            const t = triggers[index];
            captionEl.textContent = t.dataset.caption || '';
            counterEl.textContent = String(index + 1).padStart(2, '0') + ' / ' + String(triggers.length).padStart(2, '0');

            if (t.dataset.type === 'video') {
                frame.removeAttribute('src');
                showOnly(video);
                video.src = t.dataset.src;
            } else {
                stopVideo();
                showOnly(frame);
                frame.src = t.dataset.src;
                frame.alt = t.dataset.caption || 'Gallery image';
            }
        };

        const openAt = (i) => {
            lastFocus = document.activeElement;
            load(i);
            open = true;

            box.classList.remove('hidden');
            const active = isVideo() ? video : frame;

            if (!isVideo()) {
                // Images: start as the thumbnail, then glide to full screen
                const thumb = triggers[index].querySelector('img');
                const r = thumb.getBoundingClientRect();
                frame.style.transition = 'none';
                syncFrame({ left: r.left, top: r.top, width: r.width, height: r.height });
                frame.style.opacity = '1';
                void frame.offsetWidth; // reflow

                if (!reduceMotion) frame.style.transition = 'left .55s cubic-bezier(0.22,1,0.36,1), top .55s cubic-bezier(0.22,1,0.36,1), width .55s cubic-bezier(0.22,1,0.36,1), height .55s cubic-bezier(0.22,1,0.36,1)';
            } else {
                // Videos: fade in centered at a fixed 16:9 fit
                active.style.transition = 'none';
                active.style.opacity = '0';
                syncFrame(targetRect());
                void active.offsetWidth;
                if (!reduceMotion) active.style.transition = 'opacity .3s ease';
                requestAnimationFrame(() => { active.style.opacity = '1'; });
            }

            syncFrame(targetRect());
            requestAnimationFrame(() => fadeUi(true));
            document.body.style.overflow = 'hidden';
            box.querySelector('[data-lb-close]').focus({ preventScroll: true });
        };

        const close = () => {
            if (!open) return;
            open = false;
            fadeUi(false);

            if (!isVideo()) {
                const thumb = triggers[index].querySelector('img');
                const r = thumb.getBoundingClientRect();
                frame.style.transition = reduceMotion ? 'none' : 'left .45s cubic-bezier(0.22,1,0.36,1), top .45s cubic-bezier(0.22,1,0.36,1), width .45s cubic-bezier(0.22,1,0.36,1), height .45s cubic-bezier(0.22,1,0.36,1), opacity .3s ease';
                syncFrame({ left: r.left, top: r.top, width: r.width, height: r.height });
            } else {
                video.style.transition = reduceMotion ? 'none' : 'opacity .3s ease';
                video.style.opacity = '0';
            }

            setTimeout(() => {
                if (open) return;
                box.classList.add('hidden');
                frame.style.transition = 'none';
                stopVideo();
                document.body.style.overflow = '';
                if (lastFocus) lastFocus.focus({ preventScroll: true });
            }, reduceMotion ? 0 : 460);
        };

        const step = (dir) => {
            if (!open) return;
            const active = isVideo() ? video : frame;
            active.style.opacity = '0';
            setTimeout(() => {
                load(index + dir);
                const next = isVideo() ? video : frame;
                if (isVideo()) {
                    syncFrame(targetRect());
                    next.style.opacity = '1';
                } else {
                    const ready = () => { syncFrame(targetRect()); next.style.opacity = '1'; };
                    frame.complete ? ready() : frame.addEventListener('load', ready, { once: true });
                }
            }, 140);
        };

        triggers.forEach((t, i) => t.addEventListener('click', () => openAt(i)));
        box.querySelector('[data-lb-close]').addEventListener('click', close);
        box.querySelector('[data-lb-prev]').addEventListener('click', () => step(-1));
        box.querySelector('[data-lb-next]').addEventListener('click', () => step(1));
        box.querySelector('[data-lb-backdrop]').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (!open) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') step(-1);
            if (e.key === 'ArrowRight') step(1);
        });
        window.addEventListener('resize', () => { if (open) syncFrame(targetRect()); });
    })();
    </script>
@endsection