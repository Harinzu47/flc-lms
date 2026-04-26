<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin Task Manager — full CRUD for the task catalogue.
 *
 * Architecture mirrors Admin\MaterialManager exactly:
 *  - #[Validate] attributes on form properties (Livewire 3 style).
 *  - Modal open/close state owned by Livewire, bridged to Alpine via @entangle.
 *  - WithPagination resets to page 1 after every mutation.
 *  - deadline stored as a string (Y-m-d\TH:i) for datetime-local input
 *    compatibility; cast to Carbon by the Task model on persist.
 */
#[Layout('layouts.base')]
#[Title('Task Manager — FLC Admin')]
class TaskManager extends Component
{
    use WithPagination;

    // ── UI State ───────────────────────────────────────────────────────────────

    public bool $isModalOpen = false;
    public ?int $editId      = null;

    // ── Form Properties ────────────────────────────────────────────────────────

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $description = '';

    #[Validate('required|in:essay,file_upload,quiz')]
    public string $type = 'essay';

    #[Validate('required|integer|min:1|max:9999')]
    public int $base_xp = 50;

    /**
     * Stored as a string so the datetime-local input can bind to it directly.
     * Validated as a date, persisted as a Carbon via the model cast.
     */
    #[Validate('nullable|date')]
    public string $deadline = '';

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function render(): View
    {
        /** @var LengthAwarePaginator<Task> $tasks */
        $tasks = Task::latest()->paginate(10);

        return view('livewire.task-manager', compact('tasks'));
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /** Open the modal in "create" mode. */
    public function create(): void
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    /** Open the modal pre-filled with an existing Task's data. */
    public function edit(Task $task): void
    {
        $this->resetForm();
        $this->editId      = $task->id;
        $this->title       = $task->title;
        $this->description = $task->description;
        $this->type        = $task->type;
        $this->base_xp     = $task->base_xp;
        // Format deadline for the datetime-local input (Y-m-d\TH:i)
        $this->deadline = $task->deadline
            ? $task->deadline->format('Y-m-d\TH:i')
            : '';
        $this->isModalOpen = true;
    }

    /** Validate and persist — creates or updates depending on $editId. */
    public function save(): void
    {
        $this->validate();

        $data = [
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type,
            'base_xp'     => $this->base_xp,
            'deadline'    => $this->deadline ?: null,
        ];

        if ($this->editId !== null) {
            Task::findOrFail($this->editId)->update($data);
            $message = 'Task updated successfully.';
        } else {
            Task::create($data);
            $message = 'Task created successfully.';
        }

        $this->closeModal();
        $this->resetPage();
        $this->dispatch('notify', message: $message);
    }

    /** Permanently delete a task. wire:confirm handles the browser dialog. */
    public function delete(Task $task): void
    {
        $task->delete();
        $this->resetPage();
        $this->dispatch('notify', message: "'{$task->title}' has been deleted.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editId      = null;
        $this->title       = '';
        $this->description = '';
        $this->type        = 'essay';
        $this->base_xp     = 50;
        $this->deadline    = '';
        $this->resetValidation();
    }
}
