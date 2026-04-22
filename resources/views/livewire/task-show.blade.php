{{--
    Task Submission — FLC UMJ Gamified LMS
    ────────────────────────────────────────────────────────────────────────────
    Design:   Stitch AI · Screen ID: b4452b7a1fee40c9b803e06622c96a4d
    Backend:  App\Livewire\TaskShow
    Layout:   layouts.gamified (owns the Top App Bar + Sidebar chrome)
    ────────────────────────────────────────────────────────────────────────────

    This view is a coordinator only. All presentational logic lives in partials
    under resources/views/livewire/partials/task/.

    Livewire scope note: @include shares the parent component's variable scope,
    so wire:model / wire:click directives in partials bind to TaskShow directly.
    ────────────────────────────────────────────────────────────────────────────
--}}

{{-- ── Sidebar: Task-specific navigation injected into the layout ──────────── --}}
@section('sidebar-title')
    <h2 class="text-blue-800 font-bold font-headline">Task Submission</h2>
    <p class="text-xs text-on-surface-variant mt-0.5">{{ $task->title }}</p>
@endsection

@section('sidebar-nav')
    <a href="#task-overview"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">assignment</span>
        <span>Overview</span>
    </a>
    <a href="#task-instructions"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">description</span>
        <span>Instructions</span>
    </a>
    <a href="#task-form"
       class="flex items-center gap-3 bg-surface-container-lowest text-primary rounded-xl px-4 py-3 shadow-sm font-medium">
        <span class="material-symbols-outlined" aria-hidden="true">upload_file</span>
        <span>Submissions</span>
    </a>
    <a href="#"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">grade</span>
        <span>Grades</span>
    </a>
    <a href="#"
       class="flex items-center gap-3 text-on-surface-variant px-4 py-3 hover:bg-blue-50/50 rounded-xl transition-all">
        <span class="material-symbols-outlined" aria-hidden="true">forum</span>
        <span>Feedback</span>
    </a>
@endsection

{{-- Sidebar quick-submit CTA (only before first submission) --}}
@if(! $existingSubmission)
    @section('sidebar-cta')
        <div class="mt-auto p-4 bg-primary-container/10 rounded-2xl border border-primary/5">
            <button
                wire:click="submitTask"
                wire:loading.attr="disabled"
                class="w-full bg-primary text-on-primary py-3 rounded-xl font-headline font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                aria-label="Submit your final draft"
            >
                <span wire:loading.remove wire:target="submitTask">Submit Final Draft</span>
                <span wire:loading wire:target="submitTask">Submitting...</span>
            </button>
        </div>
    @endsection
@endif

{{-- ── MAIN CONTENT ────────────────────────────────────────────────────────── --}}
<div class="max-w-6xl mx-auto">

    {{-- Task Header: breadcrumbs, H1, XP/deadline pill badges --}}
    @include('livewire.partials.task.header')

    <div class="grid grid-cols-12 gap-12 items-start mt-8">

        {{-- ── LEFT COLUMN: Task Info & Instructions ──────────────────────── --}}
        <div class="col-span-12 lg:col-span-7 space-y-10">

            {{-- Instructions Card --}}
            <section class="bg-surface-container-low rounded-3xl p-8 relative overflow-hidden"
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
                    {!! $task->description !!}
                </div>
            </section>

            {{-- Deadline / Time Remaining Card --}}
            @if($task->deadline)
                @php
                    $now      = now();
                    $deadline = $task->deadline;
                    $isPast   = $now->gt($deadline);
                    $diff     = $now->diff($deadline);
                @endphp
                <section class="bg-surface-container-lowest rounded-3xl p-8 shadow-sm border border-outline-variant/10 flex items-center justify-between"
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

                    {{-- Countdown ring (decorative) --}}
                    <div class="w-20 h-20 relative flex-shrink-0" aria-hidden="true">
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
                </section>
            @endif

        </div>{{-- /left col --}}

        {{-- ── RIGHT COLUMN: Submission Card ──────────────────────────────── --}}
        <div class="col-span-12 lg:col-span-5" id="task-form">
            <div class="sticky top-28 bg-surface-container-lowest rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-white">

                @if(! $existingSubmission)
                    {{-- Upload / Essay form --}}
                    @include('livewire.partials.task.upload-form')
                @else
                    {{-- Read-only submitted state --}}
                    @include('livewire.partials.task.submitted-state')
                @endif

                {{-- ── Student Profile Mini-Card ──────────────────────────── --}}
                <div class="mt-6 bg-surface-bright rounded-2xl p-5 border border-outline-variant/10"
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
        </div>{{-- /right col --}}

    </div>{{-- /grid --}}
</div>{{-- /max-w-6xl --}}
