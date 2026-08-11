<x-app-layout>
    <x-slot name="header">
        <span>Edit Page</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 w-full" value="{{ old('title', $page->title) }}" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" value="Slug" />
                <x-text-input id="slug" name="slug" class="mt-1 w-full" value="{{ old('slug', $page->slug) }}" required />
                <p class="mt-1 text-xs text-gray-500">Unique. Lowercase letters, numbers and dashes. Changing it keeps old links working via redirects.</p>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="parent_id" value="Parent Page" />
                <x-select-input name="parent_id" :options="$pages->where('id', '!=', $page->id)->pluck('title', 'id')" :selected="old('parent_id', $page->parent_id)" placeholder="None (top level)" />
                <p class="mt-1 text-xs text-gray-500">Parent only affects the admin tree; page URLs stay at /about-us/... regardless.</p>
                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
            </div>

            @include('admin.partials.url-preview', ['model' => $page])

            <div>
                <x-input-label for="excerpt" value="Excerpt" />
                <x-textarea-input name="excerpt" :value="old('excerpt', $page->excerpt)" rows="2" />
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="content" value="Content (HTML)" />
                <x-textarea-input name="content" :value="old('content', $page->content)" rows="12" />
                <p class="mt-1 text-xs text-gray-500">Raw HTML. Rendered inside the editorial content area.</p>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cover_image" value="Cover Image" />
                <x-image-input name="cover_image" :value="old('cover_image', $page->cover_image)" />
                <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full" value="{{ old('sort_order', $page->sort_order) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
