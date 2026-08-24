@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="AST Photo Gallery"
        lede="Moments from the mountains, the jungles and the valleys we call home."
        image="/assets/images/cover-all.png" />

    <section class="spatial-explorer relative mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="eyebrow eyebrow-royal">Spatial Explorer</p>
                <h2 class="mt-1 font-serif text-2xl font-bold tracking-tight text-ink sm:text-3xl">Pan the map, zoom to explore</h2>
                <p class="mt-2 max-w-2xl text-sm text-ink-faint">Click a frame to move the viewport, scroll to zoom, or use the arrow keys.</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-ink-faint">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-paper-soft px-3 py-1.5"><kbd class="rounded bg-ink/10 px-1.5 py-0.5 text-[10px]">←</kbd><kbd class="rounded bg-ink/10 px-1.5 py-0.5 text-[10px]">→</kbd> pan</span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-paper-soft px-3 py-1.5"><kbd class="rounded bg-ink/10 px-1.5 py-0.5 text-[10px]">scroll</kbd> zoom</span>
            </div>
        </div>

        <div class="spatial-explorer loading relative overflow-hidden rounded-card border border-line bg-[#0a0a0a] shadow-2xl">
            <div class="viewport relative h-[78vh] min-h-[480px] max-h-[760px] w-full">
                <div class="canvas" style="--x:0;--y:0;">
                    @php $cells = collect(range(1,16)); @endphp
                    @foreach ($cells as $i)
                        @php $img = $images->get($i - 1); @endphp
                        <div class="cell{{ $i === 1 ? ' selected' : '' }}">
                            @if ($img)
                                <img src="{{ $img->image_url }}" alt="{{ $img->caption ?? 'Gallery image' }}" loading="lazy">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="focus-container">
                    <button class="focus" aria-labelledby="ally-zoom">
                        <span class="visuallyhidden" id="ally-zoom">expand image</span>
                    </button>
                </div>

                <div class="map-container">
                    <div class="map">
                        @foreach ($cells as $i)
                            <div></div>
                        @endforeach
                    </div>
                </div>

                <button class="back" id="back">close</button>
            </div>
        </div>
    </section>

    <script>
    (function () {
        const root = document.querySelector('.spatial-explorer');
        if (!root) return;
        const GRID = 4;
        const clamp = (a, min = 0, max = GRID - 1) => Math.min(max, Math.max(min, a));
        const index = (el) => [...el.parentElement.children].indexOf(el);

        const viewport = root.querySelector('.viewport');
        const canvas = root.querySelector('.canvas');
        const map = root.querySelector('.map');
        const focus = root.querySelector('.focus');
        const back = root.querySelector('#back');
        let item = canvas.querySelector('.selected');
        let id = index(item);
        let transitioning = false;
        let coords = { x: 0, y: 0 };

        const translate = () => {
            canvas.style.setProperty('--x', coords.x);
            canvas.style.setProperty('--y', coords.y);
        };
        const panningTo = (i) => {
            coords.x = i % GRID;
            coords.y = Math.floor(i / GRID);
            translate();
        };
        const makeSelection = (sel) => {
            if (transitioning) return;
            item.classList.remove('selected');
            if (map) map.children[index(item)].classList.remove('selected');
            item = canvas.children[sel];
            item.classList.add('selected');
            if (map) map.children[sel].classList.add('selected');
            id = index(item);
            panningTo(id);
        };
        const handleSelection = (ev) => {
            if (transitioning) return;
            if (ev.target === item) return;
            if (ev.target === ev.currentTarget || ev.target.nodeName === 'IMG') return;
            makeSelection(index(ev.target));
        };
        const toggleZoom = (ev) => {
            if (ev) ev.preventDefault();
            if (transitioning) return;
            root.classList.toggle('open');
        };
        const handleZoom = (ev) => {
            viewport.style.setProperty('--zoom', ev.target.value);
        };
        const handleWheel = (ev) => {
            if (transitioning) return;
            let v = parseFloat(zoomInput.value) + ev.deltaY * -0.05;
            v = clamp(v, parseFloat(zoomInput.min), parseFloat(zoomInput.max));
            zoomInput.value = v;
            viewport.style.setProperty('--zoom', v);
        };
        const handleKeyboard = (ev) => {
            if (transitioning) return;
            let x = coords.x, y = coords.y, idx = id;
            switch (ev.keyCode) {
                case 37: x--; break; // left
                case 39: x++; break; // right
                case 38: y--; break; // up
                case 40: y++; break; // down
                case 13: toggleZoom(ev); return;
                default: return;
            }
            x = clamp(x); y = clamp(y);
            coords.x = x; coords.y = y;
            idx = y * GRID + x;
            makeSelection(idx);
        };

        canvas.addEventListener('click', handleSelection, false);
        if (map) map.addEventListener('click', handleSelection, false);
        if (focus) focus.addEventListener('click', toggleZoom, false);
        if (back) back.addEventListener('click', toggleZoom, false);
        if (zoomInput) zoomInput.addEventListener('input', handleZoom);
        document.addEventListener('keydown', handleKeyboard);
        root.addEventListener('wheel', handleWheel, { passive: true });

        canvas.addEventListener('transitionrun', () => { transitioning = true; });
        canvas.addEventListener('transitionend', () => { transitioning = false; });
        viewport.addEventListener('transitionrun', () => { transitioning = true; });
        viewport.addEventListener('transitionend', () => { transitioning = false; });

        panningTo(id);
        if (map) map.children[id].classList.add('selected');

        // mark loaded once images are ready
        const imgs = canvas.querySelectorAll('img');
        let loaded = 0;
        if (imgs.length === 0) { root.classList.remove('loading'); root.classList.add('loaded'); }
        else {
            imgs.forEach((img) => {
                if (img.complete) { loaded++; }
                else img.addEventListener('load', () => { loaded++; if (loaded === imgs.length) done(); });
            });
            if (loaded === imgs.length) done();
        }
        function done() {
            setTimeout(() => { root.classList.remove('loading'); root.classList.add('loaded'); }, 300);
        }
    })();
    </script>
@endsection