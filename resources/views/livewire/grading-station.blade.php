{{--
    Grading Station — FLC UMJ Admin
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: 4674a34a60d7456dbdd5c27972191b11
    Backend:  App\Livewire\GradingStation
    Layout:   layouts.base (bare HTML shell — admin uses its own 3-panel chrome)
    ────────────────────────────────────────────────────────────────────────────

    This view is a coordinator only. Partials live in:
      resources/views/livewire/partials/grading/

    All partials share this component's variable scope via @include, so
    wire:model / wire:click directives resolve against GradingStation directly.
    ────────────────────────────────────────────────────────────────────────────
--}}

@push('styles')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e0e3e5; border-radius: 10px; }
    </style>
@endpush

{{-- ── Alpine Root: Toast listener ──────────────────────────────────────────── --}}
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
    class="bg-background text-on-surface font-body min-h-screen flex"
>

    {{-- ── TOAST ────────────────────────────────────────────────────────────── --}}
    <div
        x-show="toastVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-6 right-6 z-[9999] flex items-center gap-3 bg-gradient-to-br from-primary to-primary-container text-on-primary px-6 py-4 rounded-2xl shadow-[0_10px_25px_-5px_rgba(43,75,185,0.45)]"
        role="alert"
        aria-live="polite"
    >
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">verified</span>
        <div>
            <p class="font-headline font-bold text-base leading-none" x-text="toastMessage"></p>
            <p class="text-on-primary/75 text-sm mt-0.5">XP has been awarded to the student.</p>
        </div>
    </div>

    {{-- ── PANEL 1: Admin Sidebar ───────────────────────────────────────────── --}}
    @include('livewire.partials.admin.sidebar', ['activePage' => 'grading'])

    {{-- ── MAIN CONTENT (offset for fixed sidebar) ─────────────────────────── --}}
    <main class="pl-64 min-h-screen flex flex-col w-full">

        {{-- Top App Bar --}}
        <header class="sticky top-0 z-40 flex items-center justify-between px-8 py-3 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-6">
                <h1 class="text-xl font-bold text-blue-800 font-headline tracking-tight">FLC UMJ</h1>
                <div class="relative hidden lg:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                          style="font-size: 18px;"
                          aria-hidden="true">search</span>
                    <input
                        class="bg-surface-container-low border-none rounded-full pl-10 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-primary/20 transition-all"
                        placeholder="Search submissions..."
                        type="search"
                        aria-label="Search submissions"
                    >
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-surface-container-low transition-colors"
                        aria-label="Notifications">
                    <span class="material-symbols-outlined text-on-surface-variant" aria-hidden="true">notifications</span>
                </button>
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-surface-container-low transition-colors"
                        aria-label="Settings">
                    <span class="material-symbols-outlined text-on-surface-variant" aria-hidden="true">settings</span>
                </button>
                <div class="h-8 w-px bg-slate-200 mx-2" aria-hidden="true"></div>
                <div class="flex items-center gap-3 cursor-pointer group">
                    <span class="text-sm font-semibold text-blue-700 font-headline">{{ auth()->user()->name }}</span>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-sm border-2 border-white shadow-sm"
                         aria-label="{{ auth()->user()->name }}">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Grading Station: Two-panel layout ───────────────────────────── --}}
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden bg-background">

            {{-- PANEL 2: Submission Viewer --}}
            <section class="flex-1 flex flex-col lg:flex-row overflow-hidden"
                     aria-label="Submission viewer">

                {{-- PANEL 2a: Queue List (narrow column) --}}
                @include('livewire.partials.grading.queue-list')

                {{-- PANEL 2b: Submission Content Area --}}
                <div class="flex-1 p-8 lg:overflow-y-auto custom-scrollbar">

                    @if(! $selectedSubmission)
                        {{-- No submission selected --}}
                        @include('livewire.partials.grading.empty-state', ['variant' => 'content'])
                    @else
                        {{-- Submission Detail View --}}
                        <div class="max-w-3xl mx-auto space-y-8">

                            {{-- Submission Header --}}
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full text-xs font-bold tracking-wider uppercase font-label">
                                            Under Review
                                        </span>
                                        <span class="text-on-surface-variant text-sm font-medium">
                                            Submitted {{ $selectedSubmission->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                    <h2 class="text-3xl font-extrabold font-headline text-on-surface tracking-tight leading-tight">
                                        {{ $selectedSubmission->task->title }}
                                    </h2>
                                    <p class="text-lg text-on-surface-variant mt-2 font-body">
                                        Student: <span class="font-semibold text-primary">{{ $selectedSubmission->user->name }}</span>
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-6">
                                    <span class="text-xs font-label text-on-surface-variant uppercase tracking-widest">Max XP</span>
                                    <div class="text-3xl font-headline font-bold text-tertiary">
                                        {{ $selectedSubmission->task->base_xp }} XP
                                    </div>
                                </div>
                            </div>

                            {{-- Submission Body --}}
                            <div class="bg-surface-container-lowest rounded-2xl p-8 shadow-sm border border-slate-100/50">
                                <h3 class="text-xs font-label text-on-surface-variant uppercase tracking-widest mb-6">
                                    Submitted Response
                                </h3>

                                @if($selectedSubmission->answer_text)
                                    <div class="prose prose-slate max-w-none text-on-surface leading-relaxed font-body">
                                        <p>{{ $selectedSubmission->answer_text }}</p>
                                    </div>
                                @else
                                    <p class="text-on-surface-variant italic">No written answer provided (file submission).</p>
                                @endif

                                {{-- File Attachment --}}
                                @if($selectedSubmission->file_url)
                                    <div class="mt-10 pt-8" style="border-top: 1px solid rgba(195,198,215,0.3);">
                                        <div class="flex items-center justify-between p-4 bg-surface-container-low rounded-xl">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center text-primary shadow-sm"
                                                     aria-hidden="true">
                                                    <span class="material-symbols-outlined text-3xl">description</span>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-on-surface">{{ basename($selectedSubmission->file_url) }}</p>
                                                    <p class="text-xs text-on-surface-variant uppercase tracking-tighter">Submitted File</p>
                                                </div>
                                            </div>
                                            <a href="{{ Storage::url($selectedSubmission->file_url) }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="px-5 py-2.5 bg-surface-container-highest text-on-primary-fixed-variant rounded-xl font-bold flex items-center gap-2 hover:bg-surface-container-high transition-all text-sm"
                                               aria-label="Download {{ basename($selectedSubmission->file_url) }}">
                                                <span class="material-symbols-outlined text-lg" aria-hidden="true">download</span>
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Decorative Design Element --}}
                            <div class="grid grid-cols-12 gap-4 h-48" aria-hidden="true">
                                <div class="col-span-8 rounded-2xl overflow-hidden relative bg-surface-container-low flex items-center justify-center">
                                    <span class="material-symbols-outlined text-8xl text-outline-variant/40">auto_stories</span>
                                    <div class="absolute inset-0 bg-gradient-to-t from-primary/30 to-transparent"></div>
                                </div>
                                <div class="col-span-4 rounded-2xl overflow-hidden bg-primary p-6 flex flex-col justify-end text-on-primary">
                                    <span class="material-symbols-outlined text-4xl mb-4">auto_awesome</span>
                                    <h4 class="text-lg font-bold font-headline leading-tight">Ready to Grade</h4>
                                    <p class="text-sm opacity-80 mt-1">Score this submission in the panel →</p>
                                </div>
                            </div>

                        </div>{{-- /max-w-3xl --}}
                    @endif

                </div>{{-- /submission content --}}
            </section>{{-- /panel 2 --}}

            {{-- PANEL 3: Grading Panel (right) --}}
            <section class="w-full lg:w-[400px] bg-surface-container-low p-8 border-l border-slate-200/60 shadow-inner custom-scrollbar overflow-y-auto flex-shrink-0"
                     aria-label="Grading panel">
                <div class="sticky top-6">
                    <div class="bg-surface-container-lowest rounded-2xl shadow-xl shadow-blue-900/5 p-8 border border-white">

                        {{-- Panel Header --}}
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-2 h-8 bg-primary rounded-full" aria-hidden="true"></div>
                            <h3 class="text-2xl font-bold font-headline tracking-tight">Grading Panel</h3>
                        </div>

                        @if(! $selectedSubmission)
                            {{-- No submission selected — compact placeholder --}}
                            @include('livewire.partials.grading.empty-state', ['variant' => 'panel'])
                        @else
                            {{-- Grade form with wire:model, quick tags, submit button --}}
                            @include('livewire.partials.grading.grading-panel')
                        @endif

                    </div>{{-- /grading card --}}
                </div>{{-- /sticky --}}
            </section>{{-- /panel 3 --}}

        </div>{{-- /flex-1 flex-row --}}
    </main>{{-- /pl-64 --}}

</div>{{-- /x-data Alpine root --}}
