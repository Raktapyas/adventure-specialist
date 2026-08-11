<x-app-layout>
    <x-slot name="header">
        <span>Edit Service</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 w-full" value="{{ old('title', $service->title) }}" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label value="Slug" />
                <x-text-input class="mt-1 w-full bg-gray-100" value="{{ $service->slug }}" disabled />
                <p class="mt-1 text-xs text-gray-500">Immutable. Changing it would break public URLs.</p>
            </div>

            <div>
                <x-input-label value="Parent Service" />
                <x-text-input class="mt-1 w-full bg-gray-100" value="{{ $service->parent?->title ?? 'None (top level)' }}" disabled />
                <p class="mt-1 text-xs text-gray-500">Immutable. Changing it would break public URLs.</p>
            </div>

            <div>
                <x-input-label for="excerpt" value="Excerpt" />
                <x-textarea-input name="excerpt" :value="old('excerpt', $service->excerpt)" rows="2" />
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="content" value="Content (HTML)" />
                <x-textarea-input name="content" :value="old('content', $service->content)" rows="12" />
                <p class="mt-1 text-xs text-gray-500">Raw HTML. Rendered inside the editorial content area.</p>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cover_image" value="Cover Image" />
                <x-image-input name="cover_image" :value="old('cover_image', $service->cover_image)" />
                <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full" value="{{ old('sort_order', $service->sort_order) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.services.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
