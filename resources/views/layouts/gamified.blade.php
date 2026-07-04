<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' . config('app.name', 'FLC LMS') : config('app.name', 'FLC LMS') }}</title>

        {{-- Vite: Tailwind CSS + Alpine.js (bundled via Livewire's @livewire directive) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Google Fonts: Manrope (headline) + Public Sans (body) + Material Symbols --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Public+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

        {{-- Per-page styles pushed by child components --}}
        @stack('styles')

        {{-- Livewire 3 styles --}}
        @livewireStyles
    </head>

    <body class="font-sans antialiased bg-background text-on-surface" x-data="{ sidebarOpen: false }">

        {{-- Global Toast Notification --}}
        <x-toast-notification />

        {{-- ── TOP APP BAR ──────────────────────────────────────────────── --}}
        <x-app-navbar />

        {{-- ── SIDEBAR ──────────────────────────────────────────────────── --}}
        {{--
            The sidebar is intentionally generic here. Each full-page
            component can push a @section('sidebar-title') / @section('sidebar-content')
            if it needs a custom sidebar. Otherwise the default renders.
        --}}
        <aside class="fixed left-0 top-20 flex flex-col h-[calc(100vh-5rem)] p-4 bg-slate-50 w-64 font-label z-40 transition-transform duration-300 transform md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
               @click.away="sidebarOpen = false"
               aria-label="Page navigation">

            <div class="mb-8 px-4">
                @hasSection('sidebar-title')
                    @yield('sidebar-title')
                @else
                    <h2 class="text-blue-800 font-bold font-headline">
                        {{ config('app.name', 'FLC LMS') }}
                    </h2>
                @endif
            </div>

            <nav class="flex flex-col gap-2">
                @hasSection('sidebar-nav')
                    @yield('sidebar-nav')
                @else
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                @endif
            </nav>

            {{-- Optional bottom CTA injected by child components --}}
            @hasSection('sidebar-cta')
                @yield('sidebar-cta')
            @endif
        </aside>

        {{-- ── MAIN CONTENT AREA ────────────────────────────────────────── --}}
        <main class="md:ml-64 pt-24 px-8 pb-12 min-h-screen">
            {{ $slot }}
        </main>

        {{-- ── Background Decoration (Stitch Academic Prestige) ─────────── --}}
        <div class="fixed top-0 right-0 -z-10 opacity-30 pointer-events-none" aria-hidden="true">
            <svg fill="none" height="600" viewBox="0 0 600 600" width="600" xmlns="http://www.w3.org/2000/svg">
                <circle cx="450" cy="150" fill="url(#gamified_bg_gradient)" r="150"/>
                <defs>
                    <linearGradient gradientUnits="userSpaceOnUse" id="gamified_bg_gradient" x1="450" x2="450" y1="0" y2="300">
                        <stop stop-color="#4865d3"/>
                        <stop offset="1" stop-color="white" stop-opacity="0"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <livewire:celebration-hub />

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    </body>
</html>
