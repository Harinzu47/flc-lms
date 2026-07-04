<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Concerns;

use App\Models\Course;
use App\Models\Material;
use App\Models\Task;
use Illuminate\Support\Facades\Gate;

trait ManagesMaterialsAndTasks
{
    // ── Material CRUD Actions ────────────────────────────────────────────────
    public function createMaterial(int $moduleId): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetMaterialForm();
        $this->materialModuleId = $moduleId;
        $this->isMaterialModalOpen = true;
    }

    public function editMaterial(Material $material): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetMaterialForm();
        $this->materialId = $material->id;
        $this->materialModuleId = $material->module_id;
        $this->materialTitle = $material->title;
        $this->materialDescription = $material->description ?? '';
        $this->materialFileUrl = $material->file_url ?? '';
        $this->materialType = $material->type;
        $this->materialXpReward = $material->xp_reward;
        $this->isMaterialModalOpen = true;
    }

    public function saveMaterial(): void
    {
        Gate::authorize('manage', Course::class);
        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialDescription' => ['nullable', 'string', 'max:20000'],
            'materialFileUrl' => ['nullable', 'url', 'max:500'],
            'materialType' => ['required', 'in:video,document,link,article'],
            'materialXpReward' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $data = [
            'module_id' => $this->materialModuleId,
            'title' => $this->materialTitle,
            'description' => $this->materialDescription ?: null,
            'file_url' => $this->materialFileUrl ?: null,
            'type' => $this->materialType,
            'xp_reward' => $this->materialXpReward,
        ];

        if ($this->materialId !== null) {
            Material::findOrFail($this->materialId)->update($data);
            $message = 'Material updated successfully.';
        } else {
            Material::create($data);
            $message = 'Material created successfully.';
        }

        $this->closeMaterialModal();
        $this->dispatch('notify', message: $message);
    }

    public function deleteMaterial(Material $material): void
    {
        Gate::authorize('manage', Course::class);
        $material->delete();
        $this->dispatch('notify', message: 'Material deleted.');
    }

    public function closeMaterialModal(): void
    {
        $this->isMaterialModalOpen = false;
        $this->resetMaterialForm();
    }

    protected function resetMaterialForm(): void
    {
        $this->materialId = null;
        $this->materialModuleId = null;
        $this->materialTitle = '';
        $this->materialDescription = '';
        $this->materialFileUrl = '';
        $this->materialType = 'document';
        $this->materialXpReward = 10;
        $this->resetValidation();
    }

    // ── Task CRUD Actions ────────────────────────────────────────────────────
    public function createTask(int $moduleId): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetTaskForm();
        $this->taskModuleId = $moduleId;
        $this->isTaskModalOpen = true;
    }

    public function editTask(Task $task): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetTaskForm();
        $this->taskId = $task->id;
        $this->taskModuleId = $task->module_id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description;
        $this->taskType = $task->type;
        $this->taskBaseXp = $task->base_xp;
        $this->taskDaysLimit = $task->days_limit;
        $this->isTaskModalOpen = true;
    }

    public function saveTask(): void
    {
        Gate::authorize('manage', Course::class);
        $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['required', 'string', 'max:2000'],
            'taskType' => ['required', 'in:essay,file_upload,quiz'],
            'taskBaseXp' => ['required', 'integer', 'min:1', 'max:9999'],
            'taskDaysLimit' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $data = [
            'module_id' => $this->taskModuleId,
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'type' => $this->taskType,
            'base_xp' => $this->taskBaseXp,
            'days_limit' => $this->taskDaysLimit,
        ];

        if ($this->taskId !== null) {
            Task::findOrFail($this->taskId)->update($data);
            $message = 'Task updated successfully.';
        } else {
            Task::create($data);
            $message = 'Task created successfully.';
        }

        $this->closeTaskModal();
        $this->dispatch('notify', message: $message);
    }

    public function deleteTask(Task $task): void
    {
        Gate::authorize('manage', Course::class);
        $task->delete();
        $this->dispatch('notify', message: 'Task deleted.');
    }

    public function closeTaskModal(): void
    {
        $this->isTaskModalOpen = false;
        $this->resetTaskForm();
    }

    protected function resetTaskForm(): void
    {
        $this->taskId = null;
        $this->taskModuleId = null;
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskType = 'essay';
        $this->taskBaseXp = 50;
        $this->taskDaysLimit = null;
        $this->resetValidation();
    }
}
