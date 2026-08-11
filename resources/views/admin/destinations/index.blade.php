<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Destinations</span>
            <a href="{{ route('admin.destinations.create') }}" class="inline-flex items-center px-3 py-2 bg-pine text-paper text-sm font-medium rounded-md hover:bg-pine-deep">
                Add Destination
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-md bg-clay/10 text-clay border border-clay/30 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Title</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Slug</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Public URL</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sort</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($destinations as $destination)
                        <x-admin.partials.tree-rows
                            :nodes="collect([$destination])"
                            resource="destinations"
                            routeKey="destination"
                        />
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-gray-500">No destinations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
