{{--
    Task Submission — FLC UMJ Gamified LMS
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: b4452b7a1fee40c9b803e06622c96a4d
    Backend:  App\Livewire\TaskShow
    Layout:   layouts.base (owns 100% of viewport, with custom navbar)
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
            <p class="text-on-primary/75 text-sm mt-0.5">Task submission updated successfully.</p>
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
                <a class="text-on-surface-variant hover:text-primary transition-colors" href="{{ route('library') }}">Library</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Achievements</a>
            </nav>

            {{-- Right Actions --}}
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

                        <div class="px-4 py-2 border-b border-outline-variant/10 mb-1">
                            <p class="text-sm font-bold font-headline text-on-surface truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-on-surface-variant truncate">{{ auth()->user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface-variant hover:bg-blue-50/60 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">person</span>
                            Profil Saya
                        </a>

                        <div class="border-t border-outline-variant/10 my-1"></div>

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
    </header>

    {{-- ── MAIN CONTENT ────────────────────────────────────────────────────── --}}
    <main class="pt-28 pb-32 px-4">
        <div class="max-w-6xl mx-auto">

            {{-- Banner Notifikasi Revisi --}}
            @if($existingSubmission && $existingSubmission->is_flagged)
                <div class="mb-8 p-5 bg-error-container/20 border-2 border-error/25 rounded-3xl flex items-start gap-4 shadow-sm" role="alert">
                    <div class="w-10 h-10 bg-error-container text-error rounded-2xl flex items-center justify-center flex-shrink-0" aria-hidden="true">
                        <span class="material-symbols-outlined font-bold text-xl">warning</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-headline font-extrabold text-error text-lg leading-none">TUGAS BUTUH REVISI</h4>
                        <p class="text-on-surface-variant font-medium text-sm mt-2 leading-relaxed">
                            <strong>Catatan Dosen:</strong> {{ $existingSubmission->review_comment }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Task Header: breadcrumbs, H1, XP/deadline pill badges --}}
            @include('livewire.partials.task.header')

            <div class="grid grid-cols-12 gap-8 items-stretch mt-8">

                {{-- Instructions Card --}}
                <div class="col-span-12 {{ $task->deadline ? 'lg:col-span-8' : '' }}">
                    <section class="bg-surface-container-low rounded-3xl p-8 relative overflow-hidden h-full"
                             id="task-instructions"
                             aria-labelledby="instructions-heading">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-3xl"
                             aria-hidden="true"></div>
                        <h3 id="instructions-heading"
                            class="font-headline font-bold text-xl mb-4 flex items-center gap-3">
                            <span class="w-1 h-6 bg-primary rounded-full" aria-hidden="true"></span>
                            Instructions
                        </h3>
                        <div class="prose prose-slate max-w-none text-on-surface-variant leading-relaxed font-body">
                            {!! \Illuminate\Support\Str::markdown($task->description ?? '', [
                                'html_input' => 'escape',
                                'allow_unsafe_links' => false,
                            ]) !!}
                        </div>
                    </section>
                </div>

                {{-- Deadline / Time Remaining Card --}}
                @if($task->deadline)
                    @php
                        $now      = now();
                        $deadline = $task->deadline;
                        $isPast   = $now->gt($deadline);
                        $diff     = $now->diff($deadline);
                    @endphp
                    <div class="col-span-12 lg:col-span-4 flex">
                        <section class="bg-surface-container-lowest rounded-3xl p-8 shadow-sm border border-outline-variant/10 flex flex-col justify-between w-full"
                                 aria-label="Deadline information">
                            <div>
                                <p class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-1">
                                    Time Remaining
                                </p>
                                @if($isPast)
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-headline font-extrabold text-error">Deadline Passed</span>
                                    </div>
                                @else
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-3xl font-headline font-extrabold text-error">
                                            @if($diff->d > 0) {{ $diff->d }} day{{ $diff->d > 1 ? 's' : '' }}, @endif
                                            {{ $diff->h }} hour{{ $diff->h !== 1 ? 's' : '' }}
                                        </span>
                                        <span class="text-on-surface-variant text-sm">left</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Countdown ring (decorative) + Icon --}}
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-on-surface-variant text-sm font-medium">
                                    Due: {{ $task->deadline->format('d M Y, H:i') }}
                                </span>
                                <div class="w-16 h-16 relative flex-shrink-0" aria-hidden="true">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 80 80">
                                        <circle class="text-surface-container-high" cx="40" cy="40"
                                                fill="transparent" r="34" stroke="currentColor" stroke-width="8"/>
                                        <circle class="{{ $isPast ? 'text-outline-variant' : 'text-error' }}" cx="40" cy="40"
                                                fill="transparent" r="34" stroke="currentColor"
                                                stroke-dasharray="213.6" stroke-dashoffset="{{ $isPast ? 213 : 53 }}"
                                                stroke-linecap="round" stroke-width="8"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="material-symbols-outlined {{ $isPast ? 'text-outline-variant' : 'text-error' }}">
                                            alarm
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                @endif

            </div>{{-- /grid --}}

            {{-- ── ROW 2: Submission Card (Full Width) ─────────────────────────── --}}
            <div class="mt-8" id="task-form">
                <div class="bg-surface-container-lowest rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-white">

                    @if(! $existingSubmission || $existingSubmission->is_flagged)
                        {{-- Upload / Essay form (unlocked for first submit OR when flagged for revision) --}}
                        @include('livewire.partials.task.upload-form')
                    @else
                        {{-- Read-only submitted state --}}
                        @include('livewire.partials.task.submitted-state')
                    @endif

                    {{-- ── Student Profile Mini-Card ──────────────────────────── --}}
                    <div class="mt-8 bg-surface-bright rounded-2xl p-5 border border-outline-variant/10"
                         aria-label="Your profile">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-secondary-fixed bg-gradient-to-br from-primary to-primary-container flex items-center justify-center flex-shrink-0"
                                 aria-hidden="true">
                                <span class="text-on-primary font-headline font-bold text-lg">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-label font-bold text-on-surface-variant uppercase">Student Profile</p>
                                <h5 class="font-headline font-bold text-on-surface">{{ auth()->user()->name }}</h5>
                            </div>
                            <div class="ml-auto text-right">
                                <span class="text-xs font-label font-bold text-secondary">
                                    {{ auth()->user()->level ? 'LVL ' . auth()->user()->level->min_xp : 'LVL 1' }}
                                </span>
                                <div class="w-16 h-1.5 bg-surface-container-high rounded-full mt-1" aria-hidden="true">
                                    <div class="w-3/4 h-full bg-secondary rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>{{-- /submission --}}

        </div>{{-- /max-w-6xl --}}
    </main>
</div>
