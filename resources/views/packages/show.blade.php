@extends('layouts.app')

@section('title', $package->title)

@section('content')
    <x-hero
        eyebrow="Special Package"
        :title="$package->title"
        :lede="$package->excerpt"
        :image="$package->cover_image">
        @if ($package->duration_days)
            <span class="inline-block rounded-card bg-royal px-4 py-1.5 text-sm font-semibold text-white">{{ $package->duration_days }} Days</span>
        @endif
    </x-hero>

    <section class="mx-auto max-w-[1240px] px-6 py-20 lg:py-28">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <a href="/special-package/" class="group inline-flex items-center gap-2 text-sm font-semibold text-royal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-transform group-hover:-translate-x-1"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                    All packages
                </a>
            </div>

            <div class="lg:col-span-9">
                @if ($package->content)
                    <div class="prose-editorial reveal">
                        {!! $package->content !!}
                    </div>
                @else
                    <p class="text-ink-faint">Detailed information is being finalized — please contact our team for a tailored itinerary and latest updates.</p>
                @endif

                <div class="mt-14 border-t border-line pt-10">
                    <a href="/contact/#enquiry" class="btn btn-royal">
                        Enquire about this package
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
