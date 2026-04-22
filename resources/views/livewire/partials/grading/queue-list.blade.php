{{--
    Partial: Submission Queue List (Panel 2 — narrow column)
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by GradingStation component):
      $pendingSubmissions — Collection<Submission> (eager-loaded: user, task)
      $selectedSubmission — ?Submission (for active highlight)

    Livewire bindings owned here:
      wire:click="selectSubmission(id)" → calls GradingStation::selectSubmission()
    ────────────────────────────────────────────────────────────────────────────
--}}

<div class="w-full lg:w-80 bg-surface-container-low custom-scrollbar overflow-y-auto flex-shrink-0 border-r border-outline-variant/20"
     aria-label="Submission queue">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline font-bold text-on-surface text-lg">Pending Queue</h2>
            <span class="text-xs font-label font-bold text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-full"
                  aria-live="polite">
                {{ $pendingSubmissions->count() }} left
            </span>
        </div>

        @if($pendingSubmissions->isEmpty())
            {{-- Empty queue state --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <span class="material-symbols-outlined text-6xl text-outline-variant" aria-hidden="true">task_alt</span>
                <p class="font-headline font-bold text-on-surface mt-4">All Caught Up!</p>
                <p class="text-sm text-on-surface-variant mt-1">No pending submissions to grade.</p>
            </div>
        @else
            <div class="space-y-3" role="list" aria-label="Submissions awaiting grading">
                @foreach($pendingSubmissions as $submission)
                    <button
                        wire:click="selectSubmission({{ $submission->id }})"
                        role="listitem"
                        aria-pressed="{{ $selectedSubmission?->id === $submission->id ? 'true' : 'false' }}"
                        aria-label="Review submission by {{ $submission->user->name }} for {{ $submission->task->title }}"
                        class="w-full text-left p-4 rounded-2xl transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-primary/30
                            {{ $selectedSubmission?->id === $submission->id
                                ? 'bg-surface-container-lowest shadow-md border-l-4 border-primary'
                                : 'bg-surface-container-lowest hover:shadow-sm hover:border-l-4 hover:border-primary/30 border-l-4 border-transparent' }}"
                    >
                        <div class="flex items-start gap-3">
                            {{-- Student avatar --}}
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-primary-container flex items-center justify-center text-on-primary font-bold text-sm flex-shrink-0 mt-0.5"
                                 aria-hidden="true">
                                {{ strtoupper(substr($submission->user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-headline font-bold text-on-surface text-sm truncate group-hover:text-primary transition-colors">
                                    {{ $submission->user->name }}
                                </p>
                                <p class="text-xs text-on-surface-variant truncate mt-0.5">
                                    {{ $submission->task->title }}
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="px-2 py-0.5 bg-secondary-container text-on-secondary-container rounded-full text-[10px] font-bold font-label uppercase">
                                        Pending
                                    </span>
                                    <span class="text-[10px] text-on-surface-variant">
                                        {{ $submission->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors flex-shrink-0"
                                  style="font-size:16px;"
                                  aria-hidden="true">chevron_right</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>
