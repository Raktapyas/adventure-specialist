<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-950">Add Image</h1>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.gallery.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="image_url" value="Image URL" />
                <x-image-input name="image_url" :value="old('image_url')" />
                <p class="mt-1 text-xs text-gray-500">Path or absolute URL, e.g. /assets/images/foo.jpg</p>
                <x-input-error :messages="$errors->get('image_url')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="caption" value="Caption" />
                <x-textarea-input name="caption" :value="old('caption')" rows="2" />
                <x-input-error :messages="$errors->get('caption')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full" value="{{ old('sort_order', 0) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Add Image</x-primary-button>
                <a href="{{ route('admin.gallery.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
