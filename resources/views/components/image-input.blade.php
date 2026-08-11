@props([
    'name',
    'value' => '',
])

<div>
    <input
        name="{{ $name }}"
        type="text"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm w-full']) }}
        x-data="{}"
        x-on:input="$el.closest('div').querySelector('img').src = $el.value"
    >
    <div class="mt-2">
        <img
            src="{{ old($name, $value) ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22640%22 height=%22360%22%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/%3E%3C/svg%3E' }}"
            alt="Preview"
            class="h-40 w-full max-w-xs object-cover rounded-md border border-gray-200"
            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22640%22 height=%22360%22%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/%3E%3C/svg%3E'"
        >
    </div>
</div>
