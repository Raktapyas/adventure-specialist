@php
    /** @var mixed $record */
    $record = $getRecord();
    $media = $media ?? ($record instanceof \App\Models\Media ? $record : $record->media);

    if ($media !== null) {
        $url = filled($media->url()) ? url($media->url()) : null;
    } else {
        // Referencing rows without a matching Media record (legacy data)
        // still preview their stored host-relative path.
        $storedPath = $record instanceof \App\Models\Media
            ? null
            : ($record->getAttribute('image_url') ?? $record->getAttribute('image_path'));
        $url = filled((string) $storedPath) ? url($storedPath) : url('/images/placeholder.png');
    }

    $sizeClass = $sizeClass ?? 'w-full aspect-square';
@endphp

@if ($media !== null && $media->isVideo())
    {{-- Native first-frame preview (preload="metadata") + play indicator --}}
    <div class="relative overflow-hidden rounded-lg bg-gray-950 {{ $sizeClass }}">
        <video src="{{ $url }}" muted preload="metadata" playsinline class="h-full w-full object-cover"></video>
        <span class="absolute inset-0 flex items-center justify-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white ring-1 ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" />
                </svg>
            </span>
        </span>
    </div>
@else
    <img src="{{ $url }}" alt="{{ $media?->name ?? '' }}" loading="lazy" class="rounded-lg object-cover {{ $sizeClass }}">
@endif
