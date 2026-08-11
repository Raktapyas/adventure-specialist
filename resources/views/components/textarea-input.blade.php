@props([
    'name',
    'value' => '',
    'rows' => 4,
])

<textarea
    name="{{ $name }}"
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => 'border-gray-300 focus:border-pine focus:ring-pine rounded-md shadow-sm w-full']) }}
>{{ old($name, $value) }}</textarea>
