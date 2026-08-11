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
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-100">
            <aside class="w-64 shrink-0 bg-pine text-paper flex flex-col">
                <div class="px-6 py-5 border-b border-paper/10">
                    <a href="{{ route('admin.dashboard') }}" class="block font-serif text-lg leading-tight">
                        Adventure Specialist
                        <span class="block text-xs font-sans tracking-widest uppercase text-paper/60">Admin Panel</span>
                    </a>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                    <a href="{{ route('admin.dashboard') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.pages.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.pages.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Pages
                    </a>
                    <a href="{{ route('admin.services.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.services.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Services
                    </a>
                    <a href="{{ route('admin.destinations.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.destinations.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Destinations
                    </a>
                    <a href="{{ route('admin.packages.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.packages.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Packages
                    </a>
                    <a href="{{ route('admin.gallery.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.gallery.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Gallery
                    </a>
                    <a href="{{ route('admin.media.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.media.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Media
                    </a>
                    <a href="{{ route('admin.inquiries.index') }}"
                       class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.inquiries.*') ? 'bg-paper/15 text-paper' : 'text-paper/70 hover:bg-paper/10 hover:text-paper' }}">
                        Inquiries
                    </a>
                    <a href="{{ route('home') }}"
                       class="block px-3 py-2 rounded-md text-paper/70 hover:bg-paper/10 hover:text-paper">
                        View Site
                    </a>
                </nav>

                <div class="px-6 py-4 border-t border-paper/10 text-sm">
                    <p class="text-paper/60 truncate">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-paper/70 hover:text-paper">
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="px-6 py-4">
                            <h1 class="text-xl font-semibold text-gray-800">{{ $header }}</h1>
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
