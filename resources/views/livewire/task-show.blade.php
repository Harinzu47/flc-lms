{{--
    Task Submission — FLC UMJ Gamified LMS
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: b4452b7a1fee40c9b803e06622c96a4d
    Backend:  App\Livewire\TaskShow
    Layout:   layouts.base (owns 100% of viewport, with shared navbar)
    ────────────────────────────────────────────────────────────────────────────
    Toast and navbar are provided by the base layout and shared components.
    ────────────────────────────────────────────────────────────────────────────
--}}

<div class="bg-surface-bright font-body text-on-surface antialiased min-h-screen">

    {{-- ── TOP APP BAR ─────────────────────────────────────────────────────── --}}
    <x-app-navbar />

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
