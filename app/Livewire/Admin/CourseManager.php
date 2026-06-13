<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Level;
use App\Models\Material;
use App\Models\Module;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.base')]
#[Title('Course Architecture Manager — FLC Admin')]
class CourseManager extends Component
{
    use WithPagination;

    // ── Navigation State ─────────────────────────────────────────────────────
    public ?int $selectedCourseId = null; // If set, opens "Builder Mode" for this course

    // ── Course Form Properties ───────────────────────────────────────────────
    public bool $isCourseModalOpen = false;
    public ?int $courseId = null;
    public string $courseTitle = '';
    public string $courseDescription = '';
    public string $courseDifficultyLevel = 'beginner';
    public ?int $courseMinLevelRequired = null;
    public ?int $coursePrerequisiteId = null;
    public bool $courseIsPublished = false;

    // ── Module Form Properties ───────────────────────────────────────────────
    public bool $isModuleModalOpen = false;
    public ?int $moduleId = null;
    public string $moduleTitle = '';
    public string $moduleDescription = '';
    public int $moduleSortOrder = 0;

    // ── Material Form Properties ─────────────────────────────────────────────
    public bool $isMaterialModalOpen = false;
    public ?int $materialId = null;
    public ?int $materialModuleId = null;
    public string $materialTitle = '';
    public string $materialDescription = '';
    public string $materialFileUrl = '';
    public string $materialType = 'document';
    public int $materialXpReward = 10;

    // ── Task Form Properties ─────────────────────────────────────────────────
    public bool $isTaskModalOpen = false;
    public ?int $taskId = null;
    public ?int $taskModuleId = null;
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskType = 'essay';
    public int $taskBaseXp = 50;
    public string $taskDeadline = '';

    // ── Render ───────────────────────────────────────────────────────────────
    public function render(): View
    {
        if ($this->selectedCourseId !== null) {
            // Builder Mode: Eager-load course modules and nested materials/tasks
            $course = Course::query()
                ->with(['modules.materials', 'modules.tasks'])
                ->findOrFail($this->selectedCourseId);

            return view('livewire.admin.course-manager', [
                'course' => $course,
                'levels' => Level::orderBy('min_xp')->get(),
                'coursesList' => Course::where('id', '!=', $this->selectedCourseId)->orderBy('title')->get(),
            ]);
        }

        // List Mode
        $courses = Course::query()
            ->with(['minLevel', 'prerequisite'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.course-manager', [
            'courses' => $courses,
            'levels' => Level::orderBy('min_xp')->get(),
            'coursesList' => Course::orderBy('title')->get(),
        ]);
    }

    // ── Navigation Actions ───────────────────────────────────────────────────
    public function selectCourse(int $id): void
    {
        $this->selectedCourseId = $id;
        $this->resetPage();
    }

    public function deselectCourse(): void
    {
        $this->selectedCourseId = null;
        $this->resetPage();
    }

    // ── Course CRUD Actions ──────────────────────────────────────────────────
    public function createCourse(): void
    {
        $this->resetCourseForm();
        $this->isCourseModalOpen = true;
    }

    public function editCourse(Course $course): void
    {
        $this->resetCourseForm();
        $this->courseId = $course->id;
        $this->courseTitle = $course->title;
        $this->courseDescription = $course->description ?? '';
        $this->courseDifficultyLevel = $course->difficulty_level;
        $this->courseMinLevelRequired = $course->min_level_required;
        $this->coursePrerequisiteId = $course->prerequisite_course_id;
        $this->courseIsPublished = $course->is_published;
        $this->isCourseModalOpen = true;
    }

    public function saveCourse(): void
    {
        $this->validate([
            'courseTitle' => ['required', 'string', 'max:255'],
            'courseDescription' => ['nullable', 'string', 'max:1000'],
            'courseDifficultyLevel' => ['required', 'in:beginner,intermediate,advanced'],
            'courseMinLevelRequired' => ['nullable', 'integer', 'exists:levels,id'],
            'coursePrerequisiteId' => ['nullable', 'integer', 'exists:courses,id', 'different:courseId'],
        ]);

        $data = [
            'title' => $this->courseTitle,
            'description' => $this->courseDescription ?: null,
            'difficulty_level' => $this->courseDifficultyLevel,
            'min_level_required' => $this->courseMinLevelRequired ?: null,
            'prerequisite_course_id' => $this->coursePrerequisiteId ?: null,
            'is_published' => $this->courseIsPublished,
        ];

        if ($this->courseId !== null) {
            Course::findOrFail($this->courseId)->update($data);
            $message = 'Course updated successfully.';
        } else {
            Course::create($data);
            $message = 'Course created successfully.';
        }

        $this->closeCourseModal();
        $this->dispatch('notify', message: $message);
    }

    public function deleteCourse(Course $course): void
    {
        DB::transaction(function () use ($course): void {
            $course->delete();
        });

        $this->dispatch('notify', message: 'Course and all its contents deleted successfully.');
        $this->deselectCourse();
    }

    // ── Module CRUD Actions ──────────────────────────────────────────────────
    public function createModule(): void
    {
        $this->resetModuleForm();
        $this->isModuleModalOpen = true;
    }

    public function editModule(Module $module): void
    {
        $this->resetModuleForm();
        $this->moduleId = $module->id;
        $this->moduleTitle = $module->title;
        $this->moduleDescription = $module->description ?? '';
        $this->moduleSortOrder = $module->sort_order;
        $this->isModuleModalOpen = true;
    }

    public function saveModule(): void
    {
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
        DB::transaction(function () use ($module): void {
            $module->delete();
        });

        $this->dispatch('notify', message: 'Module and all its items deleted.');
    }

    public function moveModuleUp(Module $module): void
    {
        if ($module->sort_order > 0) {
            $module->decrement('sort_order');
            $this->dispatch('notify', message: 'Module moved up.');
        }
    }

    public function moveModuleDown(Module $module): void
    {
        $module->increment('sort_order');
        $this->dispatch('notify', message: 'Module moved down.');
    }

    // ── Material CRUD Actions ────────────────────────────────────────────────
    public function createMaterial(int $moduleId): void
    {
        $this->resetMaterialForm();
        $this->materialModuleId = $moduleId;
        $this->isMaterialModalOpen = true;
    }

    public function editMaterial(Material $material): void
    {
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
        $this->validate([
            'materialTitle' => ['required', 'string', 'max:255'],
            'materialDescription' => ['nullable', 'string', 'max:1000'],
            'materialFileUrl' => ['nullable', 'url', 'max:500'],
            'materialType' => ['required', 'in:video,document,link'],
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
        $material->delete();
        $this->dispatch('notify', message: 'Material deleted.');
    }

    // ── Task CRUD Actions ────────────────────────────────────────────────────
    public function createTask(int $moduleId): void
    {
        $this->resetTaskForm();
        $this->taskModuleId = $moduleId;
        $this->isTaskModalOpen = true;
    }

    public function editTask(Task $task): void
    {
        $this->resetTaskForm();
        $this->taskId = $task->id;
        $this->taskModuleId = $task->module_id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description;
        $this->taskType = $task->type;
        $this->taskBaseXp = $task->base_xp;
        $this->taskDeadline = $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '';
        $this->isTaskModalOpen = true;
    }

    public function saveTask(): void
    {
        $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['required', 'string', 'max:2000'],
            'taskType' => ['required', 'in:essay,file_upload,quiz'],
            'taskBaseXp' => ['required', 'integer', 'min:1', 'max:9999'],
            'taskDeadline' => ['nullable', 'date'],
        ]);

        $data = [
            'module_id' => $this->taskModuleId,
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'type' => $this->taskType,
            'base_xp' => $this->taskBaseXp,
            'deadline' => $this->taskDeadline ?: null,
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
        $task->delete();
        $this->dispatch('notify', message: 'Task deleted.');
    }

    // ── Modals & Resets Helpers ──────────────────────────────────────────────
    public function closeCourseModal(): void
    {
        $this->isCourseModalOpen = false;
        $this->resetCourseForm();
    }

    private function resetCourseForm(): void
    {
        $this->courseId = null;
        $this->courseTitle = '';
        $this->courseDescription = '';
        $this->courseDifficultyLevel = 'beginner';
        $this->courseMinLevelRequired = null;
        $this->coursePrerequisiteId = null;
        $this->courseIsPublished = false;
        $this->resetValidation();
    }

    public function closeModuleModal(): void
    {
        $this->isModuleModalOpen = false;
        $this->resetModuleForm();
    }

    private function resetModuleForm(): void
    {
        $this->moduleId = null;
        $this->moduleTitle = '';
        $this->moduleDescription = '';
        $this->moduleSortOrder = 0;
        $this->resetValidation();
    }

    public function closeMaterialModal(): void
    {
        $this->isMaterialModalOpen = false;
        $this->resetMaterialForm();
    }

    private function resetMaterialForm(): void
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

    public function closeTaskModal(): void
    {
        $this->isTaskModalOpen = false;
        $this->resetTaskForm();
    }

    private function resetTaskForm(): void
    {
        $this->taskId = null;
        $this->taskModuleId = null;
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskType = 'essay';
        $this->taskBaseXp = 50;
        $this->taskDeadline = '';
        $this->resetValidation();
    }
}
