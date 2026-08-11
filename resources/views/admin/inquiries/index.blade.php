<x-app-layout>
    <x-slot name="header">
        <span>Inquiries</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-md bg-moss/10 text-moss border border-moss/30 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.inquiries.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="read" class="rounded-md border-gray-300 text-sm">
                    <option value="">All read states</option>
                    <option value="0" @selected($filters['read'] === '0')>Unread only</option>
                    <option value="1" @selected($filters['read'] === '1')>Read only</option>
                </select>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, email, subject, message…" class="rounded-md border-gray-300 text-sm flex-1 min-w-[12rem]">
                <button type="submit" class="px-4 py-2 rounded-md bg-pine text-white text-sm font-medium">Filter</button>
                <a href="{{ route('admin.inquiries.index') }}" class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Reset</a>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.inquiries.bulk') }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 w-10">
                                <input type="checkbox" class="rounded border-gray-300" onclick="document.querySelectorAll('.inquiry-check').forEach(c => c.checked = this.checked)">
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Received</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($inquiries as $inquiry)
                            <tr class="{{ $inquiry->is_read ? 'hover:bg-gray-50' : 'bg-amber-50/60 hover:bg-amber-50' }}">
                                <td class="px-5 py-3">
                                    <input type="checkbox" name="ids[]" value="{{ $inquiry->id }}" class="inquiry-check rounded border-gray-300">
                                </td>
                                <td class="px-5 py-3 text-gray-900 font-medium">
                                    {{ $inquiry->name }}
                                    @unless ($inquiry->is_read)
                                        <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-xs">Unread</span>
                                    @endunless
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $inquiry->email }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $inquiry->subject ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">{{ ucfirst($inquiry->status) }}</span>
                                </td>
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
                                <td colspan="7" class="px-5 py-6 text-center text-gray-500">No inquiries match your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-gray-200 flex flex-wrap items-center gap-3">
                <select name="action" class="rounded-md border-gray-300 text-sm" required>
                    <option value="" disabled selected>Bulk action…</option>
                    <optgroup label="Read state">
                        <option value="mark_read">Mark as read</option>
                        <option value="mark_unread">Mark as unread</option>
                    </optgroup>
                    <optgroup label="Status">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">Set status: {{ ucfirst($status) }}</option>
                        @endforeach
                    </optgroup>
                    <option value="delete">Delete selected</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-md bg-gray-900 text-white text-sm font-medium" onclick="return confirm('Apply bulk action to selected inquiries?');">Apply</button>
            </div>
        </form>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $inquiries->links() }}
        </div>
    </div>
</x-app-layout>
