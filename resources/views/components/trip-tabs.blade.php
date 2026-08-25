@props(['model'])

@php
    $contentTabs = collect([
        'overview' => [
            'label' => 'Trip Overview',
            'icon' => 'heroicon-o-briefcase',
            'html' => $model->content,
        ],
        'itinerary' => [
            'label' => 'Detail Itinerary',
            'icon' => 'heroicon-o-map',
            'html' => $model->itinerary,
        ],
        'includes' => [
            'label' => 'Includes',
            'icon' => 'heroicon-o-check',
            'html' => $model->includes,
        ],
        'excludes' => [
            'label' => 'Excludes',
            'icon' => 'heroicon-o-x-mark',
            'html' => $model->excludes,
        ],
    ])->filter(fn (array $tab): bool => filled($tab['html']));

    // Land on the booking form when returning with validation errors.
    $defaultTab = $errors->any() ? 'book' : ($contentTabs->keys()->first() ?? 'book');
@endphp

<div
    x-data="{
        active: '{{ $defaultTab }}',
        keys: {{ json_encode($contentTabs->keys()->push('book')->all()) }},
        move(step) {
            const index = this.keys.indexOf(this.active);
            this.active = this.keys[(index + step + this.keys.length) % this.keys.length];
            this.$nextTick(() => this.$el.querySelector('#trip-tab-' + this.active)?.focus());
        },
    }"
    class="min-w-0"
>
    @if (request()->boolean('submitted'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var msg = 'Your booking enquiry has been received. Our team will get back to you shortly with availability and the best rates.';

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thank you!',
                        html: msg,
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#0c5adb',
                        timer: 8000,
                        timerProgressBar: true,
                    }).then(function () {
                        history.replaceState(null, '', window.location.pathname);
                    });
                } else {
                    alert(msg);
                    history.replaceState(null, '', window.location.pathname);
                }
            });
        </script>
    @endif

    <div class="overflow-x-auto border-b border-line">
        <div class="flex flex-nowrap gap-1" role="tablist" aria-label="Trip information" @keydown.arrow-right.prevent="move(1)" @keydown.arrow-left.prevent="move(-1)">
            @foreach ($contentTabs as $key => $tab)
                <button
                    type="button"
                    id="trip-tab-{{ $key }}"
                    role="tab"
                    :aria-selected="active === '{{ $key }}'"
                    :tabindex="active === '{{ $key }}' ? 0 : -1"
                    aria-controls="trip-panel-{{ $key }}"
                    @click="active = '{{ $key }}'"
                    class="-mb-px inline-flex shrink-0 items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition-colors duration-200"
                    :class="active === '{{ $key }}'
                        ? 'border-royal text-ink'
                        : 'border-transparent text-royal hover:border-royal-faint hover:text-royal-dark'"
                >
                    {{ svg($tab['icon'], 'h-4 w-4 shrink-0') }}
                    {{ $tab['label'] }}
                </button>
            @endforeach

            <button
                type="button"
                id="trip-tab-book"
                role="tab"
                :aria-selected="active === 'book'"
                :tabindex="active === 'book' ? 0 : -1"
                aria-controls="trip-panel-book"
                @click="active = 'book'"
                class="-mb-px inline-flex shrink-0 items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition-colors duration-200"
                :class="active === 'book'
                    ? 'border-royal text-ink'
                    : 'border-transparent text-royal hover:border-royal-faint hover:text-royal-dark'"
            >
                {{ svg('heroicon-o-shopping-cart', 'h-4 w-4 shrink-0') }}
                Book Now
            </button>
        </div>
    </div>

    @foreach ($contentTabs as $key => $tab)
        <div
            id="trip-panel-{{ $key }}"
            role="tabpanel"
            aria-labelledby="trip-tab-{{ $key }}"
            x-show="active === '{{ $key }}'"
            x-cloak
            class="pt-8"
            tabindex="0"
        >
            <h2 class="text-lg font-bold uppercase tracking-tight text-ink">{{ $tab['label'] }}</h2>
            <div class="prose-editorial prose-p:leading-8 prose-p:text-[15.5px] sm:prose-p:text-[16px] lg:prose-p:text-[17px] prose-p:tracking-[-0.01em] prose-li:leading-7 prose-headings:leading-tight">
                {!! $tab['html'] !!}
            </div>
        </div>
    @endforeach

    @if ($contentTabs->isEmpty())
        <div class="pt-8">
            <p class="text-ink-faint">Detailed information is being finalized — please contact our team for a tailored itinerary and latest updates.</p>
        </div>
    @endif

    <div
        id="trip-panel-book"
        role="tabpanel"
        aria-labelledby="trip-tab-book"
        x-show="active === 'book'"
        x-cloak
        class="pt-8"
        tabindex="0"
    >
        <x-booking-form :trip="$model->title" />
    </div>
</div>
