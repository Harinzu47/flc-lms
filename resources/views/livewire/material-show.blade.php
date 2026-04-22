{{--
    Material Detail — FLC UMJ Gamified LMS
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: f9abd4b4b4f94fb4bba545431e51589b
    Spec:     stitch/design/material-detail/DESIGN.md (Academic Prestige Framework)
    Backend:  App\Livewire\MaterialShow
    Action:   App\Actions\Gamification\AwardMaterialXpAction
    ────────────────────────────────────────────────────────────────────────────
--}}
@push('styles')
    {{-- Google Fonts: Manrope (Display/Headline) + Public Sans (Body/Label) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Prose typography — matches Stitch spec exactly */
        .prose h2 {
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            color: #191c1e;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            letter-spacing: -0.025em;
        }
        .prose p {
            font-family: 'Public Sans', sans-serif;
            line-height: 1.8;
            color: #434655;
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
        }
        .prose ul { list-style-type: none; padding-left: 0; margin-bottom: 2rem; }
        .prose li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.75rem;
            font-family: 'Public Sans', sans-serif;
            color: #434655;
        }
        .prose li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.6em;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #2b4bb9; /* primary */
        }
        [x-cloak] { display: none !important; }
    </style>
@endpush

{{-- ============================================================
     ROOT: Alpine.js context for the Toast notification
     Listens for the 'notify' browser event dispatched by Livewire.
     ============================================================ --}}
<div
    x-data="{
        toastVisible: false,
        toastMessage: '',
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => this.toastVisible = false, 3500);
        }
    }"
    @notify.window="showToast($event.detail.message)"
    class="bg-surface-bright font-body text-on-surface antialiased min-h-screen"
>

    {{-- ── TOAST / SNACKBAR ──────────────────────────────────────────────── --}}
    <div
        x-show="toastVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed top-24 right-6 z-[9999] flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-4 rounded-2xl shadow-[0_10px_25px_-5px_rgba(43,75,185,0.45)]"
        role="alert"
        aria-live="polite"
    >
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">auto_awesome</span>
        <div>
            <p class="font-headline font-bold text-base leading-none" x-text="toastMessage"></p>
            <p class="text-on-primary/75 text-sm mt-0.5">You've successfully completed this lesson.</p>
        </div>
    </div>

    {{-- ── TOP APP BAR (Glassmorphism) ────────────────────────────────────── --}}
    <header class="bg-white/80 backdrop-blur-md fixed top-0 w-full z-50 shadow-sm">
        <div class="flex justify-between items-center px-8 py-4 max-w-7xl mx-auto w-full">

            {{-- Brand --}}
            <div class="text-2xl font-bold tracking-tighter text-blue-800 font-headline">
                FLC UMJ
            </div>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-8 font-headline font-semibold tracking-tight">
                <a class="text-on-surface-variant hover:text-primary transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="text-primary border-b-2 border-primary pb-1" href="#">Courses</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Library</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Achievements</a>
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-4">
                <button class="material-symbols-outlined text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full transition-all duration-200">
                    notifications
                </button>
                <button class="material-symbols-outlined text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full transition-all duration-200">
                    settings
                </button>
                <div class="flex items-center gap-3 pl-4 border-l border-outline-variant/30">
                    <span class="hidden lg:block text-sm font-semibold text-on-surface-variant">
                        {{ auth()->user()->name }}
                    </span>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-sm border-2 border-primary/10">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
    <main class="pt-28 pb-32 px-4">
        <article class="max-w-3xl mx-auto">

            {{-- ── Breadcrumb / Meta ───────────────────────────────── --}}
            <div class="flex items-center gap-4 mb-6">
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold uppercase tracking-wider font-label">
                    {{ ucfirst($material->type) }}
                </span>
                <span class="text-on-surface-variant text-sm font-medium font-label uppercase tracking-widest">
                    {{ $material->created_at->format('F d, Y') }}
                </span>
            </div>

            {{-- ── Title ───────────────────────────────────────────── --}}
            <h1 class="text-4xl md:text-5xl font-extrabold font-headline text-on-surface tracking-tight mb-8 leading-[1.1]">
                {{ $material->title }}
            </h1>

            {{-- ── Hero Image / Media area ─────────────────────────── --}}
            @if($material->type === 'video' && $material->file_url)
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden mb-12 shadow-sm">
                    <video controls class="w-full h-full object-cover">
                        <source src="{{ $material->file_url }}">
                    </video>
                </div>
            @elseif($material->file_url)
                {{-- Document / Link — show a styled resource card instead of a hero image --}}
                <div class="relative w-full rounded-2xl overflow-hidden mb-12 p-6 bg-surface-container-low flex items-center gap-4">
                    <span class="material-symbols-outlined text-5xl text-primary">
                        {{ $material->type === 'link' ? 'link' : 'description' }}
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-widest font-semibold text-on-surface-variant mb-1">
                            {{ $material->type === 'link' ? 'External Resource' : 'Document' }}
                        </p>
                        <a href="{{ $material->file_url }}" target="_blank" rel="noopener"
                           class="font-headline font-bold text-primary hover:underline">
                            {{ $material->type === 'link' ? $material->file_url : 'Download / View File' }}
                            <span class="material-symbols-outlined align-middle text-sm">open_in_new</span>
                        </a>
                    </div>
                </div>
            @else
                {{-- Placeholder banner when no file is attached --}}
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden mb-12 shadow-sm bg-surface-container-low flex items-center justify-center">
                    <span class="material-symbols-outlined text-8xl text-outline-variant">article</span>
                    {{-- University Blue overlay (Design spec: 20% overlay on course images) --}}
                    <div class="absolute inset-0 bg-primary/5"></div>
                </div>
            @endif

            {{-- ── Rich Text Content ───────────────────────────────── --}}
            <section class="prose prose-slate lg:prose-xl max-w-none">
                {!! $material->description !!}
            </section>

            {{-- ── Bottom Action Bar / Gamification ───────────────── --}}
            {{-- Outer Alpine scope kept for smooth in-page "XP Added" flip     --}}
            <div class="mt-16 pt-12 border-t border-outline-variant/20">

                {{-- ── UNCLAIMED state: Show the CTA button ───────── --}}
                @if(! $hasRead)
                    <div class="flex flex-col items-center">
                        <p class="font-label text-xs font-bold text-on-surface-variant tracking-[0.2em] uppercase mb-6">
                            End of Module
                        </p>

                        <button
                            wire:click="markAsRead"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed scale-95"
                            class="group relative inline-flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-10 py-5 rounded-2xl font-headline font-bold text-lg shadow-[0_10px_25px_-5px_rgba(43,75,185,0.4)] hover:scale-[1.02] active:scale-95 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50"
                        >
                            {{-- Default (idle) state --}}
                            <span wire:loading.remove wire:target="markAsRead">
                                <span class="material-symbols-outlined">check_circle</span>
                            </span>
                            <span wire:loading.remove wire:target="markAsRead">
                                Mark as Read &amp; Claim +10 XP
                            </span>
                            <span wire:loading.remove wire:target="markAsRead"
                                  class="material-symbols-outlined text-secondary-fixed opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">
                                auto_awesome
                            </span>

                            {{-- Loading state --}}
                            <svg wire:loading wire:target="markAsRead"
                                 class="animate-spin h-5 w-5 text-on-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading wire:target="markAsRead" class="font-headline font-bold text-lg">
                                Saving...
                            </span>
                        </button>
                    </div>

                {{-- ── CLAIMED state: "XP Added" confirmation card ── --}}
                @else
                    <div class="flex flex-col items-center justify-center p-8 bg-surface-container-lowest rounded-2xl shadow-sm ring-1 ring-secondary/10">
                        <div class="w-16 h-16 bg-secondary-container text-on-secondary-container rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-4xl"
                                  style="font-variation-settings: 'FILL' 1;">
                                check_circle
                            </span>
                        </div>
                        <h3 class="font-headline text-2xl font-extrabold text-on-surface mb-1">
                            XP Added! +10 XP
                        </h3>
                        <p class="text-on-surface-variant font-medium">
                            You've successfully completed this lesson.
                        </p>
                    </div>
                @endif

            </div>{{-- /gamification --}}

        </article>
    </main>

    {{-- ── MOBILE BOTTOM NAV ────────────────────────────────────────────── --}}
    <nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center p-3 bg-surface-container-lowest border-t border-surface-container shadow-[0_-4px_20px_rgba(0,0,0,0.05)] z-50 rounded-t-2xl">
        <a href="#" class="flex flex-col items-center justify-center bg-primary/5 text-primary rounded-xl px-4 py-2 transition-all active:scale-90 duration-200">
            <span class="material-symbols-outlined">menu_book</span>
            <span class="font-label text-[10px] uppercase tracking-wider font-bold">Read</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-2 hover:text-primary transition-all">
            <span class="material-symbols-outlined">dictionary</span>
            <span class="font-label text-[10px] uppercase tracking-wider font-bold">Glossary</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-2 hover:text-primary transition-all">
            <span class="material-symbols-outlined">edit_note</span>
            <span class="font-label text-[10px] uppercase tracking-wider font-bold">Notes</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center text-on-surface-variant px-4 py-2 hover:text-primary transition-all">
            <span class="material-symbols-outlined">arrow_forward</span>
            <span class="font-label text-[10px] uppercase tracking-wider font-bold">Next</span>
        </a>
    </nav>

    {{-- ── DESKTOP FLOATING ACTION (Download PDF) ─────────────────────── --}}
    <div class="hidden md:block fixed bottom-8 right-8 z-40">
        <button class="flex items-center gap-2 bg-surface-container-lowest text-primary font-headline font-bold px-6 py-4 rounded-full shadow-lg hover:shadow-xl transition-all border border-outline-variant/10">
            <span class="material-symbols-outlined">description</span>
            Download PDF Notes
        </button>
    </div>

</div>{{-- /x-data (Alpine root) --}}
