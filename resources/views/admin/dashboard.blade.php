<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $statCards = [
                    ['label' => 'Pages', 'value' => $counts['pages'], 'accent' => 'bg-pine'],
                    ['label' => 'Services', 'value' => $counts['services'], 'accent' => 'bg-royal'],
                    ['label' => 'Destinations', 'value' => $counts['destinations'], 'accent' => 'bg-moss'],
                    ['label' => 'Packages', 'value' => $counts['packages'], 'accent' => 'bg-bronze'],
                    ['label' => 'Gallery Images', 'value' => $counts['gallery'], 'accent' => 'bg-clay'],
                    ['label' => 'Inquiries', 'value' => $counts['inquiries'], 'accent' => 'bg-ink'],
                ];
            @endphp

            @foreach ($statCards as $card)
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-md {{ $card['accent'] }}"></div>
                        <div>
                            <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Inquiries</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recentInquiries as $inquiry)
                            <tr>
                                <td class="px-5 py-3 text-gray-900">{{ $inquiry->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $inquiry->email }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $inquiry->subject ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $inquiry->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-gray-500">No inquiries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
