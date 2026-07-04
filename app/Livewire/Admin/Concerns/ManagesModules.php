<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Concerns;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

trait ManagesModules
{
    public function createModule(): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetModuleForm();
        $this->isModuleModalOpen = true;
    }

    public function editModule(Module $module): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetModuleForm();
        $this->moduleId = $module->id;
        $this->moduleTitle = $module->title;
        $this->moduleDescription = $module->description ?? '';
        $this->moduleSortOrder = $module->sort_order;
        $this->isModuleModalOpen = true;
    }

    public function saveModule(): void
    {
        Gate::authorize('manage', Course::class);
        $this->validate([
            'moduleTitle' => ['required', 'string', 'max:255'],
            'moduleDescription' => ['nullable', 'string', 'max:1000'],
            'moduleSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $data = [
            'course_id' => $this->selectedCourseId,
            'title' => $this->moduleTitle,
            'description' => $this->moduleDescription ?: null,
            'sort_order' => $this->moduleSortOrder,
        ];

        if ($this->moduleId !== null) {
            Module::findOrFail($this->moduleId)->update($data);
            $message = 'Module updated successfully.';
        } else {
            Module::create($data);
            $message = 'Module created successfully.';
        }

        $this->closeModuleModal();
        $this->dispatch('notify', message: $message);
    }

    public function deleteModule(Module $module): void
    {
        Gate::authorize('manage', Course::class);
        DB::transaction(function () use ($module): void {
            $module->delete();
        });

        $this->dispatch('notify', message: 'Module and all its items deleted.');
    }

    public function moveModuleUp(Module $module): void
    {
        Gate::authorize('manage', Course::class);
        DB::transaction(function () use ($module) {
            $previousModule = Module::query()
                ->where('course_id', $module->course_id)
                ->where('sort_order', '<', $module->sort_order)
                ->orderByDesc('sort_order')
                ->first();

            if ($previousModule) {
                $oldOrder = $module->sort_order;
                $module->update(['sort_order' => $previousModule->sort_order]);
                $previousModule->update(['sort_order' => $oldOrder]);
                $this->dispatch('notify', message: 'Module moved up.');
            }
        });
    }

    public function moveModuleDown(Module $module): void
    {
        Gate::authorize('manage', Course::class);
        DB::transaction(function () use ($module) {
            $nextModule = Module::query()
                ->where('course_id', $module->course_id)
                ->where('sort_order', '>', $module->sort_order)
                ->orderBy('sort_order')
                ->first();

            if ($nextModule) {
                $oldOrder = $module->sort_order;
                $module->update(['sort_order' => $nextModule->sort_order]);
                $nextModule->update(['sort_order' => $oldOrder]);
                $this->dispatch('notify', message: 'Module moved down.');
            }
        });
    }

    public function closeModuleModal(): void
    {
        $this->isModuleModalOpen = false;
        $this->resetModuleForm();
    }

    protected function resetModuleForm(): void
    {
        $this->moduleId = null;
        $this->moduleTitle = '';
        $this->moduleDescription = '';
        $this->moduleSortOrder = 0;
        $this->resetValidation();
    }
}
