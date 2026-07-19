<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'FLC LMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Public+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-background text-on-surface min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary shadow-xl mb-8">
            <span class="material-symbols-outlined text-on-primary text-4xl" style="font-variation-settings:'FILL' 1;">school</span>
        </div>
        <h1 class="font-headline font-extrabold text-4xl md:text-5xl text-on-surface tracking-tight mb-4">
            FLC UMJ LMS
        </h1>
        <p class="text-on-surface-variant text-lg max-w-md mx-auto mb-10">
            Gamified Learning Management System — Universitas Muhammadiyah Jakarta
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary px-8 py-4 rounded-xl font-headline font-bold shadow-lg shadow-primary/25 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">dashboard</span>
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary px-8 py-4 rounded-xl font-headline font-bold shadow-lg shadow-primary/25 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">login</span>
                    Sign In
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 bg-surface-container-lowest text-primary px-8 py-4 rounded-xl font-headline font-bold border border-outline-variant/20 hover:bg-surface-container-low transition-all">
                        Create Account
                    </a>
                @endif
            @endauth
        </div>
    </div>
</body>
</html>
