<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FLC LMS') }}</title>

        <!-- Google Fonts: Manrope + Public Sans + Material Symbols -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Public+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-background text-on-surface">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            {{-- Brand Header --}}
            <div>
                <a href="/" class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-primary flex items-center justify-center shadow-lg">
                        <span class="material-symbols-outlined text-on-primary text-2xl"
                              style="font-variation-settings:'FILL' 1;">school</span>
                    </div>
                    <div>
                        <p class="font-headline font-extrabold text-lg leading-none text-on-surface">FLC UMJ</p>
                        <p class="text-on-surface-variant text-xs mt-0.5">Learning Management System</p>
                    </div>
                </a>
            </div>

            {{-- Card --}}
            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-surface-container-lowest shadow-xl overflow-hidden sm:rounded-2xl border border-outline-variant/10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
