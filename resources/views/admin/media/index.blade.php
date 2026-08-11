<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Media Library</span>
            <a href="{{ route('admin.media.create') }}" class="inline-flex items-center px-3 py-2 bg-pine text-paper text-sm font-medium rounded-md hover:bg-pine-deep">
                Upload Images
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-md bg-clay/10 text-clay border border-clay/30 text-sm">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.media.index') }}" class="mb-5 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search by name or path…"
               class="border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm w-72">
        <select name="type" class="border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm">
            <option value="">All types</option>
            @foreach (['jpg' => 'JPEG', 'png' => 'PNG', 'webp' => 'WebP', 'gif' => 'GIF'] as $value => $label)
                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="source" class="border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm">
            <option value="">All sources</option>
            <option value="uploaded" @selected($filters['source'] === 'uploaded')>Uploaded</option>
            <option value="legacy" @selected($filters['source'] === 'legacy')>Legacy</option>
        </select>
        <button type="submit" class="inline-flex items-center px-3 py-2 bg-paper text-pine border border-gray-200 text-sm font-medium rounded-md hover:bg-gray-50">Filter</button>
        @if ($filters['search'] || $filters['type'] || $filters['source'])
            <a href="{{ route('admin.media.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse ($media as $item)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img src="{{ $item->url() }}" alt="{{ $item->alt_text ?? $item->name }}" loading="lazy"
                         class="w-full h-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="p-4 space-y-1">
                    <p class="text-sm text-gray-900 font-medium truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                    <p class="text-xs text-gray-500 font-mono truncate" title="{{ $item->path }}">{{ $item->path }}</p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                        <span>{{ strtoupper($item->extension) }} · {{ $item->humanSize() }}</span>
                        @if ($item->is_legacy)
                            <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">legacy</span>
                        @endif
                        <span>{{ $item->usages_count }} usage{{ $item->usages_count === 1 ? '' : 's' }}</span>
                    </div>
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-400">{{ $item->uploader?->name ?? '—' }}</span>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" class="inline"
                              onsubmit="return confirm('Remove this media item?{{ $item->is_legacy ? ' The original file will be kept in place.' : '' }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-clay hover:underline text-sm font-medium">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-sm p-6 text-center text-gray-500">
                No media found. @if (request('search') || request('type') || request('source'))<a href="{{ route('admin.media.index') }}" class="text-pine hover:underline">Clear filters</a>.@endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $media->links() }}
    </div>
</x-app-layout>
