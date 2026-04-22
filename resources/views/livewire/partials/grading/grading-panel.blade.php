{{--
    Partial: Grading Panel (Panel 3 — right column)
    ────────────────────────────────────────────────────────────────────────────
    Rendered when $selectedSubmission is NOT null.

    Livewire bindings owned here:
      wire:model="scoreForm.{id}"                → GradingStation::$scoreForm
      wire:click="$set('scoreForm.{id}', value)" → Quick tag shortcut
      wire:click="submitGrade"                   → GradingStation::submitGrade()
      wire:loading.*                             → Loading state directives

    Context variables (provided by GradingStation component):
      $selectedSubmission — Submission (with task, user)
      $scoreForm          — array<int, string>
    ────────────────────────────────────────────────────────────────────────────
--}}

{{-- Validation error --}}
@error("scoreForm.{$selectedSubmission->id}")
    <div class="flex items-center gap-3 bg-error-container text-on-error-container px-4 py-3 rounded-xl mb-6"
         role="alert">
        <span class="material-symbols-outlined text-error" aria-hidden="true">error</span>
        <p class="text-sm font-medium">{{ $message }}</p>
    </div>
@enderror

<div class="space-y-6">

    {{-- Score Input --}}
    <div class="space-y-2">
        <label for="score-input-{{ $selectedSubmission->id }}"
               class="text-xs font-label text-on-surface-variant uppercase tracking-widest block">
            Final Score (0–100)
        </label>
        <div class="relative">
            <input
                id="score-input-{{ $selectedSubmission->id }}"
                wire:model="scoreForm.{{ $selectedSubmission->id }}"
                type="number"
                min="0"
                max="100"
                placeholder="0"
                class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 text-2xl font-bold font-headline focus:ring-2 focus:ring-primary/30 transition-all text-primary"
                aria-describedby="xp-preview-{{ $selectedSubmission->id }}"
            >
            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold text-lg"
                  aria-hidden="true">%</span>
        </div>

        {{-- Live XP Preview --}}
        @if(! empty($scoreForm[$selectedSubmission->id]) && is_numeric($scoreForm[$selectedSubmission->id]))
            @php
                $previewXp = (int) round(
                    (min(100, max(0, (int) $scoreForm[$selectedSubmission->id])) / 100)
                    * $selectedSubmission->task->base_xp
                );
            @endphp
            <p id="xp-preview-{{ $selectedSubmission->id }}"
               class="mt-2 text-xs font-medium text-secondary flex items-center gap-1"
               aria-live="polite">
                <span class="material-symbols-outlined"
                      style="font-size:14px; font-variation-settings:'FILL' 1;"
                      aria-hidden="true">bolt</span>
                This will award <strong>{{ $previewXp }} XP</strong> to {{ $selectedSubmission->user->name }}
            </p>
        @endif
    </div>

    {{-- Info Note --}}
    <div class="flex gap-3 p-4 bg-primary/5 rounded-xl border border-primary/10" role="note">
        <span class="material-symbols-outlined text-primary text-xl flex-shrink-0" aria-hidden="true">info</span>
        <p class="text-sm text-on-surface-variant italic leading-snug">
            Grading this will trigger the XP reward calculation:
            <strong>XP = (score / 100) × {{ $selectedSubmission->task->base_xp }}</strong>
        </p>
    </div>

    {{-- Quick Quality Tags (score shortcuts) --}}
    <div class="space-y-2">
        <label class="text-xs font-label text-on-surface-variant uppercase tracking-widest block">
            Quick Tags
        </label>
        <div class="flex flex-wrap gap-2" role="group" aria-label="Score shortcuts">
            @foreach(['100' => 'Exemplary', '75' => 'Satisfactory', '50' => 'Needs Revision', '30' => 'Insufficient'] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('scoreForm.{{ $selectedSubmission->id }}', '{{ $value }}')"
                    class="px-3 py-1 bg-surface-container-high text-on-surface-variant rounded-full text-xs font-medium cursor-pointer hover:bg-primary hover:text-on-primary transition-colors"
                    aria-label="Set score to {{ $value }} ({{ $label }})"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Submit Grade Button --}}
    <button
        wire:click="submitGrade"
        wire:loading.attr="disabled"
        wire:loading.class="opacity-60 cursor-not-allowed !scale-100"
        class="w-full bg-gradient-to-br from-primary to-primary-container text-on-primary py-5 rounded-xl font-bold text-lg shadow-lg shadow-primary/25 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-4 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-primary/50"
        aria-label="Save grade for {{ $selectedSubmission->user->name }}"
    >
        <span class="relative z-10 flex items-center gap-2" wire:loading.remove wire:target="submitGrade">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;" aria-hidden="true">verified</span>
            Save Grade
        </span>
        <svg wire:loading wire:target="submitGrade"
             class="animate-spin h-5 w-5 text-on-primary" fill="none" viewBox="0 0 24 24"
             aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span wire:loading wire:target="submitGrade" class="font-bold text-lg">Saving...</span>
        {{-- Hover overlay --}}
        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true"></div>
    </button>

</div>{{-- /space-y-6 --}}

{{-- Footer Actions --}}
<div class="mt-8 pt-6 flex justify-between items-center" style="border-top: 1px solid rgba(195,198,215,0.4);">
    <button class="text-on-surface-variant hover:text-error flex items-center gap-2 text-sm font-semibold transition-colors">
        <span class="material-symbols-outlined text-lg" aria-hidden="true">flag</span>
        Flag for Review
    </button>
    <div class="flex items-center gap-2 text-outline-variant" aria-hidden="true">
        <span class="material-symbols-outlined text-lg">history</span>
        <span class="text-xs">Ready to grade</span>
    </div>
</div>
