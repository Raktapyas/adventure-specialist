<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-xl font-semibold text-gray-950">Packages</h2>
            <a href="{{ route('admin.packages.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium hover:bg-amber-500">
                Add Package
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-green-50 text-green-700 border border-green-200 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Title</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Slug</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Days</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sort</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($packages as $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-900">{{ $package->title }}</td>
                            <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $package->slug }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $package->duration_days ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $package->sort_order }}</td>
                            <td class="px-5 py-3">@if ($package->is_published)<span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700 text-xs">Published</span>@else<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-xs">Draft</span>@endif</td>
                            <td class="px-5 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('admin.packages.edit', $package) }}" class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="inline" onsubmit="return confirm('Delete this package?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:underline text-sm font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-gray-500">No packages yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
