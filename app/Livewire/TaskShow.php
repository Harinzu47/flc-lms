<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\LMS\SubmitTaskAction;
use App\Models\Submission;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Full-page Livewire component for the Task Submission screen.
 *
 * Stitch AI Screen ID: b4452b7a1fee40c9b803e06622c96a4d
 *
 * Responsibilities (thin component):
 *   - Route model binding for the Task.
 *   - Deriving $existingSubmission on mount (single query).
 *   - Conditional input validation based on task type.
 *   - Delegating submission persistence to SubmitTaskAction.
 */
#[Layout('layouts.gamified')]
#[Title('Task Submission — FLC LMS')]
class TaskShow extends Component
{
    use WithFileUploads;

    // ── Route model binding ───────────────────────────────────────────────────
    public Task $task;

    // ── Form state ────────────────────────────────────────────────────────────
    public string $answerText = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $uploadedFile = null;

    // ── Derived state (set in mount, refreshed after submission) ─────────────
    public ?Submission $existingSubmission = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Lifecycle
    // ─────────────────────────────────────────────────────────────────────────

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->loadExistingSubmission();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate and submit the task.
     *
     * Validation rules are dynamically derived from the task type:
     *   - 'essay'       → answerText required
     *   - 'file_upload' → uploadedFile required, max 2 MB
     *   - 'quiz'        → no submission form (handled elsewhere)
     *
     * SubmitTaskAction is injected by the service container.
     */
    public function submitTask(SubmitTaskAction $action): void
    {
        // ── Dynamic validation ────────────────────────────────────────────────
        $rules = match ($this->task->type) {
            'essay' => [
                'answerText' => ['required', 'string', 'min:10'],
            ],
            'file_upload' => [
                'uploadedFile' => ['required', 'file', 'max:2048'], // 2 MB
            ],
            default => [],
        };

        $this->validate($rules);

        // ── Delegate to Action ────────────────────────────────────────────────
        try {
            $action->execute(
                user:       auth()->user(),
                task:       $this->task,
                answerText: $this->task->type === 'essay' ? $this->answerText : null,
                file:       $this->task->type === 'file_upload'
                                ? $this->uploadedFile?->getRealPath() !== null
                                    ? $this->uploadedFile   // Livewire wraps this as TemporaryUploadedFile
                                    : null
                                : null,
            );
        } catch (Throwable $e) {
            $this->addError('submit', $e->getMessage());
            return;
        }

        // ── Post-submission state refresh ─────────────────────────────────────
        $this->reset('answerText', 'uploadedFile');
        $this->loadExistingSubmission();

        $this->dispatch('notify', message: 'Task Submitted! Waiting for grading.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function loadExistingSubmission(): void
    {
        $this->existingSubmission = Submission::query()
            ->where('user_id', auth()->id())
            ->where('task_id', $this->task->id)
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        return view('livewire.task-show');
    }
}
