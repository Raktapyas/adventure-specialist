@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
    <x-hero
        eyebrow="Adventure Specialist Travel"
        title="Contact Us"
        lede="We would love to hear from you. Send us a message and our team will respond as soon as possible."
        image="https://adventurespecialist.com.np/wp-content/themes/trekking/images/1.jpg" />

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-14 lg:grid-cols-12">
            {{-- Contact details --}}
            <div class="lg:col-span-5">
                <x-section-heading eyebrow="Get in touch" title="We are here to help" />

                <address class="mt-10 space-y-8 text-base not-italic">
                    <div class="reveal flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-royal/10 text-royal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        </span>
                        <div>
                            <p class="eyebrow">Visit</p>
                            <p class="mt-3 text-ink-soft">ADVENTURE SPECIALIST TRAVEL<br>Bungamati, Lalitpur, Nepal</p>
                        </div>
                    </div>
                    <div class="reveal flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-royal/10 text-royal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        </span>
                        <div>
                            <p class="eyebrow">Call</p>
                            <p class="mt-3 text-ink-soft">
                                <a href="tel:+97715173283" class="hover:text-royal">+977 1 5173283</a><br>
                                <a href="tel:+9779851024546" class="hover:text-royal">+977 9851024546 (M.D. Raj K. Shrestha)</a>
                            </p>
                        </div>
                    </div>
                    <div class="reveal flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-royal/10 text-royal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        </span>
                        <div>
                            <p class="eyebrow">Email</p>
                            <p class="mt-3 text-ink-soft">
                                <a href="mailto:adventurespecialisttravel@gmail.com" class="hover:text-royal">adventurespecialisttravel@gmail.com</a>
                            </p>
                        </div>
                    </div>
                    <div class="reveal flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-royal/10 text-royal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <div>
                            <p class="eyebrow">Office hours</p>
                            <p class="mt-3 text-ink-soft">Sun – Fri 9.00 – 16.00<br>Saturday – CLOSED</p>
                        </div>
                    </div>
                    <div class="reveal flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-royal/10 text-royal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </span>
                        <div>
                            <p class="eyebrow">Managing Director</p>
                            <p class="mt-3 text-ink-soft">
                                <a href="/contact/managing-director/" class="hover:text-royal">Mr. Raj Kumar Shrestha</a>
                            </p>
                        </div>
                    </div>
                </address>
            </div>

            {{-- Form --}}
            <div class="lg:col-span-7" id="enquiry">
                <div class="rounded-card border border-line bg-paper-soft p-8 shadow-card sm:p-12">
                    @if (session('success'))
                        <div role="alert" class="mb-8 rounded-card border border-royal/30 bg-royal/5 px-5 py-4 text-sm text-royal-ink">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mb-8 rounded-card border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                            <p class="font-medium">Please fix the following:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-2 block text-sm font-semibold text-ink">Full name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
                            </div>
                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-ink">Email address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="mb-2 block text-sm font-semibold text-ink">Phone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                    class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
                            </div>
                            <div>
                                <label for="subject" class="mb-2 block text-sm font-semibold text-ink">Subject</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                    class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">
                            </div>
                        </div>

                        <div>
                            <label for="message" class="mb-2 block text-sm font-semibold text-ink">Message</label>
                            <textarea id="message" name="message" rows="6" required
                                class="w-full rounded-card border border-line bg-paper px-4 py-3 text-sm text-ink placeholder:text-ink-faint focus:border-royal focus:outline-none focus:ring-2 focus:ring-royal/20">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-royal">
                            Send message
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path d="M3.105 2.289a.75.75 0 0 0-.826.95l1.414 4.925A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.896 28.896 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.289Z" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
