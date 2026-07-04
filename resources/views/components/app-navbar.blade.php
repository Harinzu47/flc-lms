{{--
    Component: Shared Top App Bar (Glassmorphism Navbar)
    ────────────────────────────────────────────────────────────────────────────
    Replaces duplicated navbar markup (~70 lines) across gamified.blade.php,
    material-show.blade.php, and task-show.blade.php with a single reusable
    Blade component.

    Features:
      - Glassmorphism style (glass-header)
      - Dynamic active state via request()->routeIs()
      - wire:navigate for SPA-like navigation
      - Alpine.js user dropdown with profile/logout
      - ARIA labels and keyboard support

    Usage:  <x-app-navbar />
    ────────────────────────────────────────────────────────────────────────────
--}}
<nav class="fixed top-0 w-full z-50 glass-header shadow-sm" aria-label="Top navigation">
    <div class="flex justify-between items-center w-full px-8 py-4 max-w-screen-2xl mx-auto">

        {{-- Brand --}}
        <div class="flex items-center gap-4 md:gap-8">
            {{-- Hamburger Toggle (visible on mobile/tablet) --}}
            <button @click="$dispatch('toggle-sidebar')" 
                    class="p-2 -ml-2 text-on-surface-variant hover:text-primary rounded-lg md:hidden focus:outline-none"
                    aria-label="Toggle sidebar menu">
                <span class="material-symbols-outlined block text-2xl">menu</span>
            </button>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-2xl font-bold tracking-tighter text-blue-800 font-headline">
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
                <a href="{{ route('leaderboard') }}"
                   wire:navigate
                   class="font-headline font-semibold tracking-tight transition-colors {{ request()->routeIs('leaderboard') ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }}">
                    Leaderboard
                </a>
            </div>
        </div>

        {{-- User Avatar & Dropdown --}}
        <div class="flex items-center gap-3" x-data="{ userMenuOpen: false }">
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

                    {{-- Logout --}}
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
