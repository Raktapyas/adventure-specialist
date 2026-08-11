<x-app-layout>
    <x-slot name="header">
        <span>Inquiry Detail</span>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $inquiry->subject ?? 'General Inquiry' }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Received {{ $inquiry->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.inquiries.toggle-read', $inquiry) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Mark as {{ $inquiry->is_read ? 'Unread' : 'Read' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Delete this inquiry?');">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Delete</x-danger-button>
                    </form>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-wrap items-center gap-3">
                <span class="text-sm text-gray-500 font-medium">Status:</span>
                <form method="POST" action="{{ route('admin.inquiries.status', $inquiry) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="rounded-md border-gray-300 text-sm">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1.5 rounded-md bg-pine text-white text-sm font-medium">Update</button>
                </form>
                @if (! $inquiry->is_read)
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-xs">Unread</span>
                @endif
            </div>

            <dl class="divide-y divide-gray-200 text-sm">
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Name</dt>
                    <dd class="col-span-2 text-gray-900">{{ $inquiry->name }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Email</dt>
                    <dd class="col-span-2 text-gray-900">
                        <a href="mailto:{{ $inquiry->email }}" class="text-pine hover:underline">{{ $inquiry->email }}</a>
                    </dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Phone</dt>
                    <dd class="col-span-2 text-gray-900">{{ $inquiry->phone ?? '—' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Subject</dt>
                    <dd class="col-span-2 text-gray-900">{{ $inquiry->subject ?? '—' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-gray-500 font-medium">Message</dt>
                    <dd class="col-span-2 text-gray-900 whitespace-pre-wrap">{{ $inquiry->message }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to inquiries</a>
        </div>
    </div>
</x-app-layout>
