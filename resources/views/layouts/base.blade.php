<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FLC LMS') }}</title>

        <!-- Vite: compiles Tailwind CSS + Alpine.js (via Livewire) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Google Fonts: Manrope + Public Sans + Material Symbols -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Public+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
            [x-cloak] { display: none !important; }
        </style>

        <!-- Per-page styles pushed by child components (Google Fonts, custom CSS) -->
        @stack('styles')

        <!-- Livewire styles (required for Livewire 3 full-page components) -->
        @livewireStyles
    </head>

    {{--
        No Breeze navigation. No wrapper divs.
        The component's Blade view owns 100% of the viewport.
        bg-background matches the Stitch "Academic Prestige" design system token.
    --}}
    <body class="font-sans antialiased bg-background text-on-surface">

        <x-celebration-hub />

        {{ $slot }}

        @livewireScripts
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    </body>
</html>
