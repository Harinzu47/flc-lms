<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\ManagesCourses;
use App\Livewire\Admin\Concerns\ManagesMaterialsAndTasks;
use App\Livewire\Admin\Concerns\ManagesModules;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.base')]
#[Title('Course Architecture Manager — FLC Admin')]
class CourseManager extends Component
{
    use ManagesCourses;
    use ManagesMaterialsAndTasks;
    use ManagesModules;
    use WithPagination;

    // ── Navigation State ─────────────────────────────────────────────────────
    public ?int $selectedCourseId = null; // If set, opens "Builder Mode" for this course

    // ── Instruktur Assignment Form Properties ────────────────────────────────
    public bool $isAssignInstrukturModalOpen = false;

    public ?int $assignCourseId = null;

    public array $selectedInstrukturIds = [];

    // ── Course Form Properties ───────────────────────────────────────────────
    public bool $isCourseModalOpen = false;

    public ?int $courseId = null;

    public string $courseTitle = '';

    public string $courseDescription = '';

    public string $courseDifficultyLevel = 'beginner';

    public string $courseKategoriBahasa = 'general';

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

    public ?int $taskDaysLimit = null;

    /**
     * Authorize component access.
     */
    public function mount(): void
    {
        Gate::authorize('manage', Course::class);
    }

    // ── Render ───────────────────────────────────────────────────────────────
    public function render(): View
    {
        Gate::authorize('manage', Course::class);

        if ($this->selectedCourseId !== null) {
            // Builder Mode: Eager-load course modules and nested materials/tasks
            $course = Course::query()
                ->with(['modules.materials', 'modules.tasks'])
                ->findOrFail($this->selectedCourseId);

            return view('livewire.admin.course-manager', [
                'course' => $course,
                'levels' => User::allLevels(),
                'coursesList' => Course::where('id', '!=', $this->selectedCourseId)->orderBy('title')->get(),
            ]);
        }

        // List Mode
        $query = Course::query()->with(['minLevel', 'prerequisite']);

        if (auth()->user()->isInstruktur()) {
            $query->whereHas('users', fn ($q) => $q->where('users.id', auth()->id()));
        }

        $courses = $query->latest()->paginate(10);

        return view('livewire.admin.course-manager', [
            'courses' => $courses,
            'levels' => User::allLevels(),
            'coursesList' => Course::orderBy('title')->get(),
            'instrukturList' => User::where('role', User::ROLE_INSTRUKTUR)->orderBy('name')->get(),
        ]);
    }

    // ── Navigation Actions ───────────────────────────────────────────────────
    public function selectCourse(int $id): void
    {
        $course = Course::findOrFail($id);
        Gate::authorize('manage', $course);
        $this->selectedCourseId = $id;
        $this->resetPage();
    }

    public function deselectCourse(): void
    {
        Gate::authorize('manage', Course::class);
        $this->selectedCourseId = null;
        $this->resetPage();
    }

    // ── Instruktur Assignment Actions ────────────────────────────────────────

    public function openAssignInstrukturModal(int $courseId): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->assignCourseId = $courseId;

        $course = Course::findOrFail($courseId);
        // Pre-check already assigned instruktur
        $this->selectedInstrukturIds = $course->instruktur()->pluck('users.id')->toArray();

        $this->isAssignInstrukturModalOpen = true;
    }

    public function saveInstrukturAssignments(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($this->assignCourseId !== null) {
            $course = Course::findOrFail($this->assignCourseId);

            // Only sync instruktur roles to avoid detaching pesertas
            $pesertaIds = $course->peserta()->pluck('users.id')->toArray();

            $course->users()->sync(array_merge($pesertaIds, $this->selectedInstrukturIds));

            $this->dispatch('notify', message: 'Instruktur assignment updated successfully.');
        }

        $this->closeAssignInstrukturModal();
    }

    public function closeAssignInstrukturModal(): void
    {
        $this->isAssignInstrukturModalOpen = false;
        $this->assignCourseId = null;
        $this->selectedInstrukturIds = [];
    }
}
