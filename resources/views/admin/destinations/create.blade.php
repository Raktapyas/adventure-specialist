<x-app-layout>
    <x-slot name="header">
        <span>Add Destination</span>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.destinations.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 w-full" value="{{ old('title') }}" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-1 w-full" value="{{ old('slug') }}" required />
                <p class="mt-1 text-xs text-gray-500">Unique. Lowercase letters, numbers and dashes. Can be changed later — old links keep working via redirects.</p>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_id" value="Parent Destination" />
                <x-select-input name="parent_id" :options="$destinations->pluck('title', 'id')" :selected="old('parent_id')" placeholder="None (top level)" />
                <p class="mt-1 text-xs text-gray-500">Maximum three levels of nesting. Can be changed later — URLs keep working via redirects.</p>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="excerpt" value="Excerpt" />
                <x-textarea-input name="excerpt" :value="old('excerpt')" rows="2" />
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="content" value="Content (HTML)" />
                <x-textarea-input name="content" :value="old('content')" rows="12" />
                <p class="mt-1 text-xs text-gray-500">Raw HTML. Rendered inside the editorial content area.</p>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cover_image" value="Cover Image" />
                <x-image-input name="cover_image" :value="old('cover_image')" />
                <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full" value="{{ old('sort_order', 0) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', true)) class="rounded border-gray-300 text-pine focus:ring-pine">
                    Published
                </label>
                <p class="mt-1 text-xs text-gray-500">Unpublished items are hidden from the public site and navigation.</p>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Create Destination</x-primary-button>
                <a href="{{ route('admin.destinations.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
