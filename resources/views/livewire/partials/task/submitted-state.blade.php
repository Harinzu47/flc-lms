{{--
    Partial: Submitted / Read-Only State
    ────────────────────────────────────────────────────────────────────────────
    Rendered when $existingSubmission is NOT null.

    Context variables (provided by TaskShow component):
      $existingSubmission — App\Models\Submission
      $task               — App\Models\Task (for type-specific labels)
    ────────────────────────────────────────────────────────────────────────────
--}}

<header class="mb-8">
    <h2 class="text-2xl font-headline font-bold text-on-surface">Your Submission</h2>
    <p class="text-on-surface-variant text-sm mt-1">
        Submitted {{ $existingSubmission->created_at->diffForHumans() }}
    </p>
</header>

{{-- ── Status Badge ──────────────────────────────────────────────────────────── --}}
<div class="mb-6 flex justify-center" aria-live="polite">
    @if($existingSubmission->status === 'graded')
        <div class="flex flex-col items-center gap-2 bg-secondary-container px-8 py-5 rounded-2xl"
             role="status"
             aria-label="Grade: {{ $existingSubmission->score ?? 'N/A' }} out of 100">
            <div class="w-14 h-14 bg-secondary rounded-full flex items-center justify-center" aria-hidden="true">
                <span class="material-symbols-outlined text-on-secondary text-3xl"
                      style="font-variation-settings:'FILL' 1;">grade</span>
            </div>
            <p class="font-label text-xs font-bold text-on-secondary-container uppercase tracking-widest">Final Score</p>
            <p class="font-headline font-extrabold text-4xl text-secondary">
                {{ $existingSubmission->score ?? '—' }}<span class="text-xl text-on-secondary-container/60">/100</span>
            </p>
            <span class="inline-flex items-center gap-1 bg-secondary text-on-secondary px-4 py-1.5 rounded-full text-xs font-bold font-label uppercase">
                <span class="material-symbols-outlined"
                      style="font-size:14px; font-variation-settings:'FILL' 1;"
                      aria-hidden="true">check_circle</span>
                Graded
            </span>
        </div>
    @else
        <div class="flex flex-col items-center gap-3 bg-tertiary-fixed/30 px-8 py-5 rounded-2xl w-full"
             role="status"
             aria-label="Submission status: Pending Grading">
            <div class="w-14 h-14 bg-tertiary-container/40 rounded-full flex items-center justify-center" aria-hidden="true">
                <span class="material-symbols-outlined text-tertiary text-3xl">pending</span>
            </div>
            <p class="font-label text-xs font-bold text-tertiary uppercase tracking-widest">Status</p>
            <span class="inline-flex items-center gap-1.5 bg-tertiary-fixed px-5 py-2 rounded-full text-sm font-bold font-label text-on-tertiary-fixed">
                <span class="material-symbols-outlined" style="font-size:16px;" aria-hidden="true">hourglass_top</span>
                Pending Grading
            </span>
        </div>
    @endif
</div>

{{-- ── Submitted Content ─────────────────────────────────────────────────────── --}}
<div class="space-y-5 mt-4">

    @if($existingSubmission->file_url)
        <div class="p-5 bg-surface-container-low rounded-2xl">
            <p class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-3">
                Submitted File
            </p>
            <a href="{{ Storage::url($existingSubmission->file_url) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="flex items-center gap-3 text-primary hover:underline font-medium group"
               aria-label="Open submitted file: {{ basename($existingSubmission->file_url) }}">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors"
                     aria-hidden="true">
                    <span class="material-symbols-outlined text-primary">description</span>
                </div>
                <div>
                    <p class="font-headline font-semibold text-on-surface">View Submitted File</p>
                    <p class="text-xs text-on-surface-variant">{{ basename($existingSubmission->file_url) }}</p>
                </div>
                <span class="material-symbols-outlined text-sm ml-auto text-on-surface-variant group-hover:text-primary transition-colors"
                      aria-hidden="true">open_in_new</span>
            </a>
        </div>
    @endif

    @if($existingSubmission->answer_text)
        <div class="p-5 bg-surface-container-low rounded-2xl">
            <p class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest mb-3">
                {{ $task->type === 'essay' ? 'Your Answer' : 'Additional Comments' }}
            </p>
            <p class="text-on-surface-variant leading-relaxed font-body text-sm">
                {{ $existingSubmission->answer_text }}
            </p>
        </div>
    @endif

</div>{{-- /submitted content --}}
