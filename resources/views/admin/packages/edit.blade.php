<x-app-layout>
    <x-slot name="header">
        <span>Edit Package</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 w-full" value="{{ old('title', $package->title) }}" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label value="Slug" />
                <x-text-input class="mt-1 w-full bg-gray-100" value="{{ $package->slug }}" disabled />
                <p class="mt-1 text-xs text-gray-500">Immutable. Changing it would break public URLs.</p>
            </div>

            <div>
                <x-input-label for="duration_days" value="Duration (days)" />
                <x-text-input id="duration_days" name="duration_days" type="number" class="mt-1 w-full" value="{{ old('duration_days', $package->duration_days) }}" />
                <p class="mt-1 text-xs text-gray-500">Optional.</p>
                <x-input-error :messages="$errors->get('duration_days')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="excerpt" value="Excerpt" />
                <x-textarea-input name="excerpt" :value="old('excerpt', $package->excerpt)" rows="2" />
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="content" value="Content (HTML)" />
                <x-textarea-input name="content" :value="old('content', $package->content)" rows="12" />
                <p class="mt-1 text-xs text-gray-500">Raw HTML. Rendered inside the editorial content area.</p>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cover_image" value="Cover Image" />
                <x-image-input name="cover_image" :value="old('cover_image', $package->cover_image)" />
                <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full" value="{{ old('sort_order', $package->sort_order) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.packages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
