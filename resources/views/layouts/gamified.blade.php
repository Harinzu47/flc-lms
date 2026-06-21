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

        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
            .glass-header {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(20px);
            }
            [x-cloak] { display: none !important; }
        </style>
    </head>

    <body class="font-sans antialiased bg-background text-on-surface">

        {{-- ── TOAST / SNACKBAR — listen for Livewire's "notify" event ─────── --}}
        <div
            x-data="{
                toastVisible: false,
                toastMessage: '',
                showToast(msg) {
                    this.toastMessage = msg;
                    this.toastVisible = true;
                    setTimeout(() => this.toastVisible = false, 4000);
                }
            }"
            @notify.window="showToast($event.detail.message)"
        >
            <div
                x-show="toastVisible"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed top-24 right-6 z-[9999] flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-4 rounded-2xl shadow-[0_10px_25px_-5px_rgba(43,75,185,0.45)]"
                role="alert"
                aria-live="polite"
            >
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">task_alt</span>
                <div>
                    <p class="font-headline font-bold text-base leading-none" x-text="toastMessage"></p>
                    <p class="text-on-primary/75 text-sm mt-0.5">Your submission is in the grading queue.</p>
                </div>
            </div>

            {{-- ── TOP APP BAR ──────────────────────────────────────────────── --}}
            <nav class="fixed top-0 w-full z-50 glass-header shadow-sm" aria-label="Top navigation">
                <div class="flex justify-between items-center w-full px-8 py-4 max-w-screen-2xl mx-auto">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('dashboard') }}" class="text-2xl font-bold tracking-tighter text-blue-800 font-headline">
                            FLC UMJ LMS
                        </a>
                        <div class="hidden md:flex gap-6 items-center">
                            <a href="{{ route('dashboard') }}"
                               wire:navigate
                               class="font-headline font-semibold tracking-tight transition-colors {{ request()->routeIs('dashboard') ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('library') }}"
                               wire:navigate
                               class="font-headline font-semibold tracking-tight transition-colors {{ request()->routeIs('library', 'courses.show', 'materials.show', 'tasks.show') ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }}">
                                Courses
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-3" x-data="{ userMenuOpen: false }">
                        {{-- User Avatar & Dropdown Trigger --}}
                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen"
                                    @keydown.escape.window="userMenuOpen = false"
                                    class="flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-full hover:bg-surface-container-low transition-all border border-transparent hover:border-outline-variant/20"
                                    aria-label="User menu">
                                <span class="text-sm font-semibold text-on-surface-variant font-headline hidden sm:block">
                                    {{ auth()->user()->name }}
                                </span>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-xs border-2 border-primary/10">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="userMenuOpen"
                                 x-cloak
                                 @click.away="userMenuOpen = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-outline-variant/10 py-2 z-[200]">

                                {{-- User Info Header --}}
                                <div class="px-4 py-2 border-b border-outline-variant/10 mb-1">
                                    <p class="text-sm font-bold font-headline text-on-surface truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-on-surface-variant truncate">{{ auth()->user()->email }}</p>
                                </div>

                                {{-- Profile Link --}}
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface-variant hover:bg-blue-50/60 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-lg">person</span>
                                    Profil Saya
                                </a>

                                {{-- Divider --}}
                                <div class="border-t border-outline-variant/10 my-1"></div>

                                {{-- Logout (POST form) --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50/60 transition-colors text-left">
                                        <span class="material-symbols-outlined text-lg">logout</span>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {{-- ── SIDEBAR ──────────────────────────────────────────────────── --}}
            {{--
                The sidebar is intentionally generic here. Each full-page
                component can push a @section('sidebar-title') / @section('sidebar-content')
                if it needs a custom sidebar. Otherwise the default renders.
            --}}
            <aside class="fixed left-0 top-20 flex flex-col h-[calc(100vh-5rem)] p-4 bg-slate-50 w-64 font-label"
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
            <main class="ml-64 pt-24 px-8 pb-12 min-h-screen">
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
        </div>{{-- /toast wrapper --}}

        <x-celebration-hub />

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    </body>
</html>
