{{--
    Partial: Task Upload / Submission Form
    ────────────────────────────────────────────────────────────────────────────
    Rendered when $existingSubmission is null.

    Livewire bindings owned here:
      wire:model="uploadedFile"   → bound to TaskShow::$uploadedFile
      wire:model="answerText"     → bound to TaskShow::$answerText
      wire:click="submitTask"     → calls TaskShow::submitTask()
      wire:loading.*              → Livewire loading state directives

    Context variables (provided by TaskShow component):
      $task            — App\Models\Task
      $uploadedFile    — TemporaryUploadedFile|null
    ────────────────────────────────────────────────────────────────────────────
--}}

<header class="mb-8">
    <h2 class="text-2xl font-headline font-bold text-on-surface">Your Submission</h2>
    @if($task->type === 'file_upload')
        <p class="text-on-surface-variant text-sm mt-1">Upload your file in PDF or DOCX format. Max 2 MB.</p>
    @else
        <p class="text-on-surface-variant text-sm mt-1">Write your answer directly in the text area below.</p>
    @endif
</header>

{{-- Global submission error (dispatched from submitTask on Throwable) --}}
@error('submit')
    <div class="mb-6 flex items-center gap-3 bg-error-container text-on-error-container px-4 py-3 rounded-2xl"
         role="alert">
        <span class="material-symbols-outlined text-error flex-shrink-0" aria-hidden="true">error</span>
        <p class="text-sm font-medium">{{ $message }}</p>
    </div>
@enderror

<div class="space-y-6">

    {{-- ── File Upload type ────────────────────────────────────────────────── --}}
    @if($task->type === 'file_upload')
        <div>
            <label
                for="uploadedFile"
                class="group relative bg-surface-container-low border-2 border-dashed border-outline-variant rounded-2xl p-10 transition-all hover:border-primary hover:bg-primary/5 flex flex-col items-center justify-center text-center cursor-pointer"
                :class="{ 'border-primary bg-primary/5': $wire.uploadedFile }"
            >
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-primary text-3xl" aria-hidden="true">upload_file</span>
                </div>

                {{-- File selected preview --}}
                @if($uploadedFile)
                    <h4 class="font-headline font-bold text-primary">{{ $uploadedFile->getClientOriginalName() }}</h4>
                    <p class="text-on-surface-variant text-xs mt-2">
                        {{ number_format($uploadedFile->getSize() / 1024, 1) }} KB — click to change
                    </p>
                @else
                    <h4 class="font-headline font-bold text-on-surface group-hover:text-primary transition-colors">
                        Click or drag file to upload
                    </h4>
                    <p class="text-on-surface-variant text-xs mt-2 uppercase font-label font-bold tracking-tight">
                        Maximum size: 2MB • PDF, DOCX
                    </p>
                @endif

                <input
                    id="uploadedFile"
                    type="file"
                    wire:model="uploadedFile"
                    accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="absolute inset-0 opacity-0 cursor-pointer"
                    aria-label="Upload your task file"
                >
            </label>

            {{-- Upload progress indicator --}}
            <div wire:loading wire:target="uploadedFile"
                 class="mt-2 text-xs text-primary font-medium flex items-center gap-1"
                 aria-live="polite">
                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Uploading...
            </div>

            @error('uploadedFile')
                <p class="mt-2 text-xs text-error font-medium flex items-center gap-1" role="alert">
                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Additional comments field (file_upload tasks) --}}
        <div class="space-y-2">
            <label for="answerTextComment"
                   class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest px-1">
                Additional Comments
            </label>
            <textarea
                id="answerTextComment"
                wire:model="answerText"
                rows="4"
                placeholder="Tell your instructor anything important about this draft..."
                class="w-full bg-surface-container-high border-none rounded-2xl focus:ring-2 focus:ring-primary/20 p-4 font-body placeholder:text-outline text-on-surface-variant transition-all resize-none"
            ></textarea>
        </div>

    {{-- ── Essay type ───────────────────────────────────────────────────────── --}}
    @elseif($task->type === 'essay')
        <div class="space-y-2">
            <label for="answerTextEssay"
                   class="text-xs font-label font-bold text-on-surface-variant uppercase tracking-widest px-1">
                Your Answer <span class="text-error" aria-hidden="true">*</span>
            </label>
            <textarea
                id="answerTextEssay"
                wire:model="answerText"
                rows="10"
                placeholder="Write your essay answer here..."
                class="w-full bg-surface-container-high border-none rounded-2xl focus:ring-2 focus:ring-primary/20 p-4 font-body placeholder:text-outline text-on-surface-variant transition-all resize-y"
                aria-required="true"
            ></textarea>
            @error('answerText')
                <p class="text-xs text-error font-medium flex items-center gap-1 mt-1" role="alert">
                    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

    {{-- ── Quiz / unsupported type ──────────────────────────────────────────── --}}
    @else
        <div class="text-center py-8">
            <span class="material-symbols-outlined text-5xl text-outline-variant" aria-hidden="true">quiz</span>
            <p class="mt-3 text-on-surface-variant font-medium">Quiz submissions are handled separately.</p>
        </div>
    @endif

    {{-- ── Submit Button (essay + file_upload only) ────────────────────────── --}}
    @if(in_array($task->type, ['essay', 'file_upload']))
        <div class="pt-4">
            <button
                wire:click="submitTask"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed translate-y-0 !shadow-none"
                class="w-full bg-gradient-to-br from-primary to-primary-container text-on-primary py-5 rounded-2xl font-headline font-extrabold text-lg shadow-lg shadow-primary/30 hover:-translate-y-0.5 active:translate-y-px transition-all flex items-center justify-center gap-3 focus:outline-none focus:ring-2 focus:ring-primary/50"
                aria-label="Submit your task"
            >
                <span wire:loading.remove wire:target="submitTask">Submit Task</span>
                <span wire:loading.remove wire:target="submitTask"
                      class="material-symbols-outlined text-xl"
                      aria-hidden="true">send</span>

                <svg wire:loading wire:target="submitTask"
                     class="animate-spin h-5 w-5 text-on-primary" fill="none" viewBox="0 0 24 24"
                     aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading wire:target="submitTask" class="font-headline font-bold text-lg">
                    Submitting...
                </span>
            </button>
            <p class="text-center text-[10px] text-on-surface-variant mt-4 uppercase font-label font-bold tracking-[0.1em]">
                By submitting, you agree to the Academic Integrity Policy
            </p>
        </div>
    @endif

</div>{{-- /space-y-6 --}}
