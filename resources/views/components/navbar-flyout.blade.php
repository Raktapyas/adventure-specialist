@props(['items', 'level' => 0])

@foreach($items as $item)
    @php
        $hasChildren = $item->children->isNotEmpty();
        // Only allow cascading flyout up to 3 levels total (0 -> 1 -> 2)
        $hasFlyout = $hasChildren && $level < 2;
        $isDropdown = $hasFlyout || $item->isDropdown();
        // Caret only when flyout will actually render (prevents misleading arrow at leaf level)
        $showCaret = $hasFlyout;
    @endphp
    <div class="flyout-item-wrap relative" x-data="{open:false, flip:false}" x-effect="if(open){ $nextTick(()=>{ const el=$el.querySelector(':scope > .flyout-sub'); if(el){ const r=el.getBoundingClientRect(); if(r.right > window.innerWidth - 8) flip=true; else flip=false; } })}" @mouseenter="open=true" @mouseleave="open=false" @focusin="open=true" @focusout="open=false">
        <a href="{{ $isDropdown ? 'javascript:void(0)' : $item->resolvedUrl() }}" @if($item->open_in_new_tab && !$isDropdown) target="_blank" rel="noopener" @endif class="flex items-center justify-between gap-2 border-b border-line/60 px-3.5 py-2 text-[13px] font-medium text-ink-soft transition-colors last:border-0 hover:bg-accent hover:text-white" @if($isDropdown) @click.prevent="open=!open" aria-haspopup="{{ $hasFlyout ? 'true' : 'false' }}" :aria-expanded="open.toString()" @endif>
            <span>{{ $item->label }}</span>
            @if($showCaret)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 text-ink-faint transition-colors" :class="open ? 'rotate-90 !text-white' : ''"><path fill-rule="evenodd" d="M7.21 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
            @endif
        </a>
        @if($hasFlyout)
            <div class="absolute top-0 z-[60] hidden w-[235px] flyout flyout-sub" :class="flip ? 'right-full mr-1 left-auto' : 'left-full ml-1'" x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-x-1" x-transition:enter-end="opacity-100 translate-x-0" @click.outside="open=false">
                <x-navbar-flyout :items="$item->children" :level="$level + 1" />
            </div>
        @endif
    </div>
@endforeach
