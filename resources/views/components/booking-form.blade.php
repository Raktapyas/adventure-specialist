@props(['trip'])

<form method="POST" action="{{ route('booking.store') }}" class="rounded-card border border-line bg-paper-soft p-6 sm:p-10">
    @csrf

    <h2 class="text-lg font-bold uppercase tracking-tight text-ink">Book your trip</h2>

    @if ($errors->any())
        <div role="alert" class="mt-6 rounded-card border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-medium">Please fix the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-5 sm:grid-cols-2">
        <div>
            <label for="booking-trip" class="sr-only">Trip</label>
            <input type="text" id="booking-trip" name="trip" value="{{ old('trip', $trip) }}" readonly
                class="w-full cursor-not-allowed rounded-card border border-line bg-paper px-4 py-3 text-sm font-semibold text-ink focus:outline-none">
        </div>
        <div>
            <label for="booking-name" class="sr-only">Full name</label>
            <input type="text" id="booking-name" name="name" value="{{ old('name') }}" placeholder="Full Name*" required autocomplete="name"
                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
        </div>
        <div>
            <label for="booking-email" class="sr-only">Email</label>
            <input type="email" id="booking-email" name="email" value="{{ old('email') }}" placeholder="Email*" required autocomplete="email"
                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
        </div>
        <div>
            <label for="booking-country" class="sr-only">Country</label>
            <input type="text" id="booking-country" name="country" value="{{ old('country') }}" placeholder="Country*" required autocomplete="country-name"
                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
        </div>
        <div class="sm:col-span-2">
            <label for="booking-subject" class="sr-only">Subject</label>
            <input type="text" id="booking-subject" name="subject" value="{{ old('subject') }}" placeholder="Subject*" required
                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
        </div>
        <div class="sm:col-span-2">
            <label for="booking-message" class="sr-only">Additional details</label>
            <textarea id="booking-message" name="message" rows="5" placeholder="Additional Details"
                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">{{ old('message') }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-royal mt-6 w-full">
        Book Now
    </button>
</form>
