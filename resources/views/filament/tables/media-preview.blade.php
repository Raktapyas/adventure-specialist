@php
    $url = filled($media->url()) ? url($media->url()) : url('/images/placeholder.png');
@endphp

<div class="space-y-3">
    @if ($media->isVideo())
        <video src="{{ $url }}" controls autoplay muted loop playsinline preload="metadata"
               class="max-h-[70vh] w-full rounded-lg bg-black"></video>
    @else
        <img src="{{ $url }}" alt="{{ $media->name }}" class="mx-auto max-h-[70vh] w-auto rounded-lg">
    @endif

    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $media->name }} · {{ $media->humanSize() }} · {{ $media->mime_type }}
        @if ($media->alt_text)
            · {{ $media->alt_text }}
        @endif
    </p>
</div>
