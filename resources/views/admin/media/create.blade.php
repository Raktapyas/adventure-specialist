<x-app-layout>
    <x-slot name="header">
        <span>Upload Images</span>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="media" value="Images" />
                <input
                    id="media"
                    name="media[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    multiple
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-pine file:py-2 file:px-4 file:text-paper file:hover:bg-pine-deep file:cursor-pointer"
                >
                <p class="mt-1 text-xs text-gray-500">JPEG, PNG, WebP or GIF · max 5 MB each · up to 10 at once.</p>
                @php
                    // Flatten nested media.* upload errors into individual string messages
                    // (a multi-file input produces one nested bag entry per file).
                    $mediaErrors = collect($errors->get('media'))
                        ->concat(collect($errors->get('media.*'))->flatten())
                        ->map(fn ($message) => (string) $message)
                        ->all();
                @endphp
                <x-input-error :messages="$mediaErrors" class="mt-2" />
            </div>

            <div>
                <x-input-label for="alt_text" value="Default Alt Text (optional)" />
                <x-text-input id="alt_text" name="alt_text" class="mt-1 w-full" value="{{ old('alt_text') }}" />
                <p class="mt-1 text-xs text-gray-500">Applied to all images in this batch; you can still set per-image alt text on the page using them.</p>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Upload</x-primary-button>
                <a href="{{ route('admin.media.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
