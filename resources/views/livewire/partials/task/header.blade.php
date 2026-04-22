{{--
    Partial: Task Header
    ────────────────────────────────────────────────────────────────────────────
    Context variables (provided by TaskShow component):
      $task  — App\Models\Task (with type, title, base_xp, deadline)
    ────────────────────────────────────────────────────────────────────────────
--}}

{{-- ── Breadcrumbs ──────────────────────────────────────────────────────────── --}}
<nav class="flex items-center gap-2 text-sm text-on-surface-variant mb-6 font-label tracking-wide uppercase"
     id="task-overview"
     aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}" class="hover:text-primary cursor-pointer transition-colors">Courses</a>
    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">chevron_right</span>
    <span class="hover:text-primary cursor-pointer transition-colors">{{ ucfirst($task->type) }}</span>
    <span class="material-symbols-outlined" style="font-size:14px;" aria-hidden="true">chevron_right</span>
    <span class="text-on-surface font-semibold">Task Submission</span>
</nav>

{{-- ── Task Title Block ──────────────────────────────────────────────────────── --}}
<section aria-labelledby="task-heading">
    <div class="flex items-start gap-4 mb-4">
        <div class="bg-primary/10 p-3 rounded-2xl flex-shrink-0" aria-hidden="true">
            <span class="material-symbols-outlined text-primary text-3xl">
                {{ match($task->type) {
                    'essay'       => 'history_edu',
                    'file_upload' => 'upload_file',
                    'quiz'        => 'quiz',
                    default       => 'assignment'
                } }}
            </span>
        </div>
        <div>
            <h1 id="task-heading"
                class="text-4xl font-headline font-extrabold tracking-tight text-on-surface leading-tight">
                {{ $task->title }}
            </h1>
            <p class="text-on-surface-variant mt-1 font-medium">
                FLC UMJ · {{ ucfirst($task->type) }} Task
            </p>
        </div>
    </div>

    {{-- XP & Meta Badges ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-4 mt-6" role="list" aria-label="Task metadata">
        <div class="flex items-center gap-2 bg-secondary-container px-4 py-2 rounded-full border border-secondary/10"
             role="listitem">
            <span class="material-symbols-outlined text-on-secondary-container text-sm"
                  style="font-variation-settings:'FILL' 1;"
                  aria-hidden="true">stars</span>
            <span class="text-sm font-bold text-on-secondary-container font-label uppercase">
                +{{ $task->base_xp }} XP Reward
            </span>
        </div>

        @if($task->deadline)
            <div class="flex items-center gap-2 bg-tertiary-container/10 px-4 py-2 rounded-full border border-tertiary/10"
                 role="listitem">
                <span class="material-symbols-outlined text-tertiary text-sm" aria-hidden="true">schedule</span>
                <span class="text-sm font-bold text-tertiary font-label uppercase">
                    Due {{ $task->deadline->format('d M Y, H:i') }}
                </span>
            </div>
        @endif

        <div class="flex items-center gap-2 bg-surface-container-high px-4 py-2 rounded-full" role="listitem">
            <span class="material-symbols-outlined text-on-surface-variant text-sm" aria-hidden="true">label</span>
            <span class="text-sm font-bold text-on-surface-variant font-label uppercase">
                {{ ucfirst(str_replace('_', ' ', $task->type)) }}
            </span>
        </div>
    </div>
</section>
