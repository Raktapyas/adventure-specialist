<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-xl font-semibold text-gray-950">Services</h2>
            <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium hover:bg-amber-500">
                Add Service
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-green-50 text-green-700 border border-green-200 text-sm">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-md bg-red-50 text-red-700 border border-red-200 text-sm">{{ session('error') }}</div>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($services as $service)
                        <x-admin.partials.tree-rows
                            :nodes="collect([$service])"
                            resource="services"
                            routeKey="service"
                        />
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-gray-500">No services yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
