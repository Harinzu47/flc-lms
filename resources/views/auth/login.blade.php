<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name', 'FLC LMS') }}</title>
    <meta name="description" content="Sign in to the FLC UMJ Gamified Learning Management System.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts: Manrope + Public Sans + Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Public+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Public Sans', sans-serif; }
        .font-headline { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        [x-cloak] { display: none !important; }

        /* Animated gradient orbs on the hero panel */
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50%       { transform: translateY(-20px) scale(1.04); }
        }
        @keyframes float-medium {
            0%, 100% { transform: translateY(0px) scale(1); }
            50%       { transform: translateY(-12px) scale(1.02); }
        }
        .orb-slow   { animation: float-slow   8s ease-in-out infinite; }
        .orb-medium { animation: float-medium  6s ease-in-out infinite; }

        /* Password toggle show/hide */
        .pw-toggle { cursor: pointer; }
    </style>
</head>

<body class="min-h-screen bg-background text-on-surface antialiased">

{{-- ── SPLIT-SCREEN LAYOUT ─────────────────────────────────────────────── --}}
<div class="flex min-h-screen">

    {{-- ════════════════════════════════════════════════════════════════════
         LEFT PANEL — Marketing Hero
         Hidden on mobile, visible on lg+
    ════════════════════════════════════════════════════════════════════ --}}
    <aside class="hidden lg:flex lg:w-1/2 xl:w-[55%] relative overflow-hidden
                  bg-gradient-to-br from-primary via-[#1e3a9e] to-[#0f2068]
                  flex-col justify-between p-12 text-on-primary"
           aria-label="Marketing panel">

        {{-- Decorative gradient orbs --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="orb-slow absolute -top-32 -left-32 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl"></div>
            <div class="orb-medium absolute top-1/2 -right-48 w-[400px] h-[400px] bg-secondary/10 rounded-full blur-3xl"></div>
            <div class="orb-slow absolute -bottom-48 left-1/4 w-[350px] h-[350px] bg-tertiary-fixed/10 rounded-full blur-3xl" style="animation-delay: 3s;"></div>

            {{-- Subtle dot grid --}}
            <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="dots" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.5" fill="white"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#dots)"/>
            </svg>
        </div>

        {{-- Top: Brand --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-11 h-11 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-on-primary text-2xl"
                          style="font-variation-settings:'FILL' 1;">school</span>
                </div>
                <div>
                    <p class="font-headline font-extrabold text-lg leading-none">FLC UMJ</p>
                    <p class="text-on-primary/60 text-xs mt-0.5">Learning Management System</p>
                </div>
            </div>
        </div>

        {{-- Centre: Hero copy --}}
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                <span class="material-symbols-outlined text-sm text-secondary-container"
                      style="font-variation-settings:'FILL' 1;">verified</span>
                <span class="text-xs font-headline font-bold text-secondary-fixed uppercase tracking-widest">
                    Academic Excellence
                </span>
            </div>

            <h1 class="font-headline font-extrabold text-5xl xl:text-6xl leading-[1.1] tracking-tight mb-6">
                Join the<br>
                <span class="text-tertiary-fixed">FLC UMJ</span><br>
                Community
            </h1>

            <p class="text-on-primary/70 text-lg leading-relaxed max-w-md">
                Access an elite ecosystem of academic excellence, distinguished scholarship, and modern learning pathways designed for the next generation of leaders.
            </p>

            {{-- Social proof --}}
            <div class="flex items-center gap-4 mt-8">
                {{-- Avatar stack --}}
                <div class="flex -space-x-3" aria-hidden="true">
                    @foreach(['AB', 'FU', 'MR', 'NL'] as $initials)
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-white/30 to-white/10 border-2 border-white/20 flex items-center justify-center text-[10px] font-headline font-black text-on-primary">
                            {{ $initials }}
                        </div>
                    @endforeach
                </div>
                <div>
                    <p class="font-headline font-bold text-base">2,000+ Scholars</p>
                    <p class="text-on-primary/60 text-xs">Already enrolled and learning</p>
                </div>
            </div>

            {{-- Feature pills --}}
            <div class="flex flex-wrap gap-3 mt-8">
                @foreach(['Gamified XP System', 'Badge Achievements', 'Live Rankings', 'Expert Instructors'] as $feature)
                    <span class="flex items-center gap-1.5 bg-white/10 backdrop-blur-sm text-on-primary/90 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="material-symbols-outlined text-secondary-fixed"
                              style="font-size:13px; font-variation-settings:'FILL' 1;">check_circle</span>
                        {{ $feature }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Bottom: Footer --}}
        <div class="relative z-10">
            <p class="text-on-primary/40 text-xs">
                © {{ date('Y') }} Muhammadiyah Jakarta University. All rights reserved.
            </p>
        </div>

    </aside>

    {{-- ════════════════════════════════════════════════════════════════════
         RIGHT PANEL — Login Form
    ════════════════════════════════════════════════════════════════════ --}}
    <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 lg:p-16 bg-surface-container-low min-h-screen">

        {{-- Mobile brand (only on small screens) --}}
        <div class="lg:hidden flex items-center gap-3 mb-10 self-start">
            <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-on-primary text-xl"
                      style="font-variation-settings:'FILL' 1;">school</span>
            </div>
            <span class="font-headline font-extrabold text-blue-900 text-lg">FLC UMJ</span>
        </div>

        <div class="w-full max-w-md">

            {{-- Form Header --}}
            <div class="mb-10">
                <h2 class="font-headline font-extrabold text-4xl text-on-surface tracking-tight">
                    Welcome Back
                </h2>
                <p class="text-on-surface-variant mt-2">
                    Please enter your credentials to access your dashboard.
                </p>
            </div>

            {{-- Session Status (e.g. "Your email has been verified") --}}
            @if (session('status'))
                <div class="mb-6 flex items-center gap-3 bg-secondary/10 text-secondary px-4 py-3 rounded-xl" role="alert">
                    <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;" aria-hidden="true">check_circle</span>
                    <p class="text-sm font-medium">{{ session('status') }}</p>
                </div>
            @endif

            {{-- ── LOGIN FORM ────────────────────────────────────────────── --}}
            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="space-y-5">

                    {{-- Email --}}
                    <div>
                        <label for="email"
                               class="block text-xs font-headline font-bold text-on-surface-variant uppercase tracking-widest mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline"
                                  style="font-size:18px;"
                                  aria-hidden="true">mail</span>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@flc-umj.ac.id"
                                class="w-full bg-surface-container-lowest rounded-xl pl-11 pr-4 py-3.5 text-sm text-on-surface border-none
                                       focus:ring-2 focus:ring-primary/25 transition-all placeholder:text-outline-variant
                                       @error('email') ring-2 ring-error/50 @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs text-error flex items-center gap-1" role="alert">
                                <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password"
                                   class="block text-xs font-headline font-bold text-on-surface-variant uppercase tracking-widest">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-xs text-primary font-semibold hover:underline focus:outline-none focus:underline">
                                    Forgot?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline"
                                  style="font-size:18px;"
                                  aria-hidden="true">lock</span>
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full bg-surface-container-lowest rounded-xl pl-11 pr-12 py-3.5 text-sm text-on-surface border-none
                                       focus:ring-2 focus:ring-primary/25 transition-all placeholder:text-outline-variant
                                       @error('password') ring-2 ring-error/50 @enderror"
                            >
                            {{-- Toggle visibility --}}
                            <button type="button"
                                    @click="show = !show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors pw-toggle"
                                    :aria-label="show ? 'Hide password' : 'Show password'">
                                <span class="material-symbols-outlined" style="font-size:18px;" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs text-error flex items-center gap-1" role="alert">
                                <span class="material-symbols-outlined" style="font-size:13px;" aria-hidden="true">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center gap-3">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/25 cursor-pointer"
                        >
                        <label for="remember" class="text-sm text-on-surface-variant cursor-pointer select-none">
                            Keep me signed in for 30 days
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        id="login-submit-btn"
                        class="w-full bg-gradient-to-br from-primary to-primary-container text-on-primary py-4 rounded-xl
                               font-headline font-bold text-base shadow-lg shadow-primary/25
                               hover:scale-[1.01] active:scale-[0.99] transition-all
                               focus:outline-none focus:ring-2 focus:ring-primary/50
                               flex items-center justify-center gap-2 mt-2"
                    >
                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">login</span>
                        Sign In to Dashboard
                    </button>

                </div>{{-- /space-y-5 --}}

            </form>

            {{-- Register link --}}
            @if (Route::has('register'))
                <p class="text-center text-sm text-on-surface-variant mt-8">
                    Don't have an account?
                    <a href="{{ route('register') }}"
                       class="text-primary font-bold hover:underline focus:outline-none focus:underline">
                        Request Access
                    </a>
                </p>
            @endif

            {{-- Trust badges --}}
            <div class="flex items-center justify-center gap-6 mt-10 pt-6"
                 style="border-top: 1px solid rgba(195,198,215,0.3);">
                @foreach([
                    ['icon' => 'lock',      'label' => 'Secure Login'],
                    ['icon' => 'verified',  'label' => 'Accredited'],
                    ['icon' => 'school',    'label' => 'FLC UMJ'],
                ] as $badge)
                    <div class="flex flex-col items-center gap-1 text-outline-variant">
                        <span class="material-symbols-outlined text-xl"
                              style="font-variation-settings:'FILL' 1;"
                              aria-hidden="true">{{ $badge['icon'] }}</span>
                        <span class="text-[10px] font-label uppercase tracking-widest">{{ $badge['label'] }}</span>
                    </div>
                @endforeach
            </div>

        </div>{{-- /max-w-md --}}
    </main>

</div>{{-- /split-screen --}}

@livewireScripts

</body>
</html>
