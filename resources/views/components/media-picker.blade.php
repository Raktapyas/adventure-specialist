@props(['name'])

@php
    $modalId = 'media-picker-'.str_replace(['[', ']', '.'], '-', $name);
    $pickerUrl = route('admin.media.picker-data');
@endphp

<div
    x-data="mediaPicker({
        fieldName: '{{ $name }}',
        pickerUrl: '{{ $pickerUrl }}',
    })"
>
    <button
        type="button"
        x-on:click="open()"
        class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-pine focus:border-pine"
    >
        Browse Library
    </button>

    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-gray-500 opacity-75" x-on:click="openModal = false"></div>

        <div class="relative min-h-screen flex items-start justify-center py-8 px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Select image</h3>
                    <button type="button" x-on:click="openModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                <div class="px-5 py-3 border-b">
                    <input
                        type="text"
                        x-model="search"
                        x-on:input.debounce.300ms="load(true)"
                        placeholder="Search library…"
                        class="w-full border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm text-sm"
                    >
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 p-5 max-h-96 overflow-y-auto">
                    <template x-for="item in items" :key="item.id">
                        <button
                            type="button"
                            x-on:click="select(item)"
                            class="relative rounded-md overflow-hidden border-2 text-left transition"
                            :class="selected && selected.id === item.id ? 'border-pine' : 'border-transparent hover:border-gray-300'"
                        >
                            <img :src="item.url" :alt="item.name" class="h-24 w-full object-cover bg-gray-100"
                                 x-on:error="$el.style.display = 'none'">
                            <span class="block px-1.5 py-0.5 text-[10px] text-gray-600 bg-white truncate" x-text="item.name"></span>
                            <template x-if="item.is_legacy">
                                <span class="absolute top-1 right-1 px-1 py-0.5 rounded bg-black/60 text-white text-[10px]">legacy</span>
                            </template>
                        </button>
                    </template>
                </div>

                <div class="px-5 py-3 border-t flex items-center justify-between gap-3">
                    <span class="text-xs text-gray-500" x-text="items.length + ' result(s)'"></span>
                    <div class="flex items-center gap-3">
                        <button x-show="hasMore" type="button" x-on:click="load(false)" class="text-sm text-pine hover:underline">Load more</button>
                        <button
                            type="button"
                            x-on:click="insert()"
                            :disabled="!selected"
                            class="inline-flex px-4 py-2 rounded-md text-sm font-medium bg-pine text-paper hover:bg-pine-deep disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.mediaPicker = window.mediaPicker || (function () {
        return function mediaPicker(config) {
            return {
                openModal: false,
                loading: false,
                hasMore: false,
                nextPage: 1,
                search: '',
                items: [],
                selected: null,

                url(reset) {
                    const url = new URL(config.pickerUrl, window.location.origin);
                    if (this.search.trim()) {
                        url.searchParams.set('search', this.search.trim());
                    }
                    url.searchParams.set('page', reset ? 1 : this.nextPage);
                    return url.toString();
                },

                async load(reset = true) {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const res = await fetch(this.url(reset));
                        const data = await res.json();

                        this.items = reset ? data.items : this.items.concat(data.items);
                        this.hasMore = data.has_more;
                        this.nextPage = data.next_page;
                    } catch (e) {
                        this.items = reset ? [] : this.items;
                    } finally {
                        this.loading = false;
                    }
                },

                open() {
                    this.openModal = true;
                    this.load(true);
                },

                select(item) {
                    this.selected = item;
                },

                insert() {
                    if (!this.selected) return;

                    const input = document.querySelector(`[name="${config.fieldName}"]`);
                    if (input) {
                        input.value = this.selected.url;
                        input.dispatchEvent(new Event('input', { bubbles: true }));

                        const img = input.closest('div')?.querySelector('img');
                        if (img) img.src = this.selected.url;
                    }

                    this.openModal = false;
                },
            };
        };
    })();
</script>
