<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Adventure Specialist Travel') · Adventure Specialist Travel Pvt. Ltd.</title>
        <meta name="description" content="@yield('meta_description', 'Specialist in preparing your holiday programs in Nepal, Bhutan, Sikkim, Tibet and Myanmar.')">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-paper text-ink antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[60] focus:bg-pine focus:text-paper focus:px-4 focus:py-2 focus:text-sm">
            Skip to main content
        </a>

        <x-navbar />

        <main id="main-content">
            @yield('content')
        </main>

        <x-footer />
    </body>
</html>
