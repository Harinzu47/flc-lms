{{--
    Partial: Empty State — No submission selected
    ────────────────────────────────────────────────────────────────────────────
    Rendered in both the submission content area and the grading panel
    when $selectedSubmission is null.

    Pass a $variant variable to select which flavour to render:
      @include('...empty-state', ['variant' => 'content'])  → large centred illustration
      @include('...empty-state', ['variant' => 'panel'])    → compact placeholder text

    Defaults to 'content' if $variant is not passed.
    ────────────────────────────────────────────────────────────────────────────
--}}

@php $variant = $variant ?? 'content'; @endphp

@if($variant === 'panel')
    {{-- Compact placeholder inside the Grading Panel card --}}
    <div class="text-center py-8">
        <span class="material-symbols-outlined text-5xl text-outline-variant" aria-hidden="true">pending_actions</span>
        <p class="text-on-surface-variant mt-3 text-sm font-medium">
            Select a submission from the queue to enable grading.
        </p>
    </div>
@else
    {{-- Large centred illustration in the submission content area --}}
    <div class="flex flex-col items-center justify-center h-full min-h-64 text-center"
         role="status"
         aria-label="No submission selected">
        <div class="w-24 h-24 bg-surface-container-low rounded-3xl flex items-center justify-center mb-6"
             aria-hidden="true">
            <span class="material-symbols-outlined text-5xl text-outline-variant">rate_review</span>
        </div>
        <h3 class="font-headline font-bold text-2xl text-on-surface">Select a Submission</h3>
        <p class="text-on-surface-variant mt-2 max-w-xs">
            Choose a submission from the queue on the left to begin grading.
        </p>
    </div>
@endif
