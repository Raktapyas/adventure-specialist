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
                <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" onsubmit="return confirm('Delete this inquiry?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete</x-danger-button>
                </form>
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
