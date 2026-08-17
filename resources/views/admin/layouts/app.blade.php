<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin') · Adventure Specialist Travel</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-950 dark:bg-gray-950 dark:text-gray-100">
        <div class="min-h-screen flex bg-gray-50 dark:bg-gray-950">
            <aside class="w-64 shrink-0 bg-white dark:bg-gray-900 flex flex-col border-r border-gray-200 dark:border-gray-800">
                <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.pages.dashboard') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('filament.admin.resources.pages.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.pages.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Pages
                    </a>
                    <a href="{{ route('filament.admin.resources.services.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.services.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Services
                    </a>
                    <a href="{{ route('filament.admin.resources.destinations.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.destinations.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Destinations
                    </a>
                    <a href="{{ route('filament.admin.resources.packages.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.packages.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Packages
                    </a>
                    <a href="{{ route('filament.admin.resources.gallery-images.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.gallery-images.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Gallery
                    </a>
                    <a href="{{ route('filament.admin.resources.media.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.media.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Media
                    </a>
                    <a href="{{ route('filament.admin.resources.inquiries.index') }}"
                       class="block px-3 py-2 rounded-lg {{ request()->routeIs('filament.admin.resources.inquiries.*') ? 'bg-gray-100 text-amber-600 dark:bg-gray-800 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                        Inquiries
                    </a>
                    <a href="{{ route('home') }}"
                       class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                        View Site
                    </a>
                </nav>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 text-sm">
                    <p class="text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                @isset($header)
                    <header class="bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                        <div class="px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>