<x-app-layout>
    <x-slot name="header">
        <span>Inquiries</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Received</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($inquiries as $inquiry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-900">{{ $inquiry->name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $inquiry->email }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $inquiry->subject ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $inquiry->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="text-pine hover:underline text-sm font-medium">View</a>
                                <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" class="inline" onsubmit="return confirm('Delete this inquiry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-clay hover:underline text-sm font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-gray-500">No inquiries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
