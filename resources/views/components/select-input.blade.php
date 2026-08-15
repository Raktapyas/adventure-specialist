@props([
    'name',
    'options' => [],
    'placeholder' => 'None',
    'selected' => null,
    'empty' => false,
])

<select
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm w-full']) }}
>
    <option value="" {{ $selected === null || $selected === '' ? 'selected' : '' }}>{{ $placeholder }}</option>
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" {{ (string) $selected === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
