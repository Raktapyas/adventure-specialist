@props(['items', 'level' => 0])

@foreach($items as $item)
    @php
        $hasChildren = $item->children->isNotEmpty();
        // Mobile supports 3 levels total: level 0 -> 1 -> 2, same cascading limit as desktop
        $hasFlyout = $hasChildren && $level < 2;
        $canNestFurther = $level < 2;
        $isDropdown = $item->isDropdown();
        $padding = $level === 0 ? 'pl-7' : ($level === 1 ? 'pl-10' : 'pl-12');
        $textSize = $level === 0 ? 'text-sm' : 'text-xs';
    @endphp
    @if($hasChildren)
        <div x-data="{open:false}" class="border-b border-line/20">
            <button @click="open=!open" class="flex w-full items-center justify-between rounded py-2 pr-3 {{ $padding }} {{ $textSize }} text-ink-soft hover:bg-paper-soft hover:text-accent" aria-haspopup="true" :aria-expanded="open.toString()">
                <span>{{ $item->label }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="pb-1">
                @if(!$isDropdown)
                    <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="block rounded py-1.5 pr-3 {{ $padding }} text-xs text-ink-faint hover:text-accent">View {{ $item->label }}</a>
                @endif
                @if($canNestFurther)
                    <x-navbar-mobile-flyout :items="$item->children" :level="$level + 1" />
                @else
                    @foreach($item->children as $child)
                        <a href="{{ $child->resolvedUrl() }}" @if($child->open_in_new_tab) target="_blank" rel="noopener" @endif class="block rounded py-1.5 pr-3 pl-12 text-xs text-ink-faint hover:text-accent">{{ $child->label }}</a>
                    @endforeach
                @endif
            </div>
        </div>
    @else
        <a href="{{ $item->resolvedUrl() }}" @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="block rounded py-2 {{ $padding }} pr-3 {{ $textSize }} text-ink-soft hover:bg-paper-soft hover:text-accent">{{ $item->label }}</a>
    @endif
@endforeach
