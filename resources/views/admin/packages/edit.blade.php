<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-950">Edit Package</h1>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-green-50 text-green-700 border border-green-200 text-sm">{{ session('status') }}</div>
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
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-1 w-full" value="{{ old('slug', $package->slug) }}" required />
                <p class="mt-1 text-xs text-gray-500">Unique. Lowercase letters, numbers and dashes. Changing it keeps old links working via redirects.</p>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            @include('admin.partials.url-preview', ['model' => $package])

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

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $package->is_published)) class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    Published
                </label>
                <p class="mt-1 text-xs text-gray-500">Unpublished items are hidden from the public site and navigation.</p>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.packages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
