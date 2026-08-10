@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <x-hero
        eyebrow="Contact"
        :title="$page->title" />

    <section class="mx-auto max-w-3xl px-6 py-20 lg:py-28">
        <div class="prose-editorial reveal">
            {!! $page->content !!}
        </div>
        <div class="mt-14 border-t border-line pt-10">
            <a href="/contact/" class="btn btn-royal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                Contact Us
            </a>
        </div>
    </section>
@endsection
