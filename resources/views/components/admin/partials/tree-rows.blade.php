@props([
    'nodes' => [],
    'resource',
    'routeKey',
    'depth' => 0,
])

@foreach ($nodes as $node)
    <tr class="hover:bg-gray-50">
        <td class="px-5 py-3 text-gray-900 whitespace-nowrap">
            <span class="inline-block" style="padding-left: {{ $depth * 1.5 }}rem">
                {{ $depth > 0 ? '└ ' : '' }}{{ $node->title }}
            </span>
        </td>
        <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $node->slug }}</td>
        <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $node->publicUrl() }}</td>
        <td class="px-5 py-3 text-gray-500">{{ $node->sort_order }}</td>
        <td class="px-5 py-3">@if ($node->is_published)<span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700 text-xs">Published</span>@else<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-xs">Draft</span>@endif</td>
        <td class="px-5 py-3 whitespace-nowrap text-right">
            <a href="{{ route('admin.'.$resource.'.edit', $node) }}" class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
            <form method="POST" action="{{ route('admin.'.$resource.'.destroy', $node) }}" class="inline" onsubmit="return confirm('Delete this {{ $resource }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ml-3 text-red-600 hover:underline text-sm font-medium">Delete</button>
            </form>
        </td>
    </tr>
    @if ($node->children->isNotEmpty())
        <x-admin.partials.tree-rows
            :nodes="$node->children"
            :resource="$resource"
            :route-key="$routeKey"
            :depth="$depth + 1"
        />
    @endif
@endforeach
