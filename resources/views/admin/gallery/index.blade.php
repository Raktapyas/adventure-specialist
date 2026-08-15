<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-xl font-semibold text-gray-950">Gallery</h2>
            <a href="{{ route('admin.gallery.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium hover:bg-amber-500">
                Add Image
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-green-50 text-green-700 border border-green-200 text-sm">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($images as $image)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <img src="{{ $image->image_url }}" alt="{{ $image->caption }}" class="h-48 w-full object-cover" onerror="this.style.display='none'">
                <div class="p-4">
                    <p class="text-sm text-gray-900 line-clamp-2">{{ $image->caption ?? 'No caption' }}</p>
                    <p class="mt-1 text-xs text-gray-500 font-mono truncate">{{ $image->image_url }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Sort: {{ $image->sort_order }}</span>
                        <div class="space-x-3">
                            <a href="{{ route('admin.gallery.edit', $image) }}" class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" class="inline" onsubmit="return confirm('Remove this image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow-sm p-6 text-center text-gray-500">
                No gallery images yet.
            </div>
        @endforelse
    </div>
</x-app-layout>
