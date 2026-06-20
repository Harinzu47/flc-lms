<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Course;
use App\Models\Submission;
use App\Models\XpLog;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.gamified')]
#[Title('Course Timeline — FLC UMJ')]
class CourseShow extends Component
{
    public Course $course;

    /** @var Collection<int> Completed course IDs */
    public Collection $completedCourseIds;

    /** @var Collection<int> Read material IDs */
    public Collection $readMaterialIds;

    /** @var Collection<int> Graded task IDs */
    public Collection $gradedTaskIds;

    /** @var Collection<int> Completed module IDs */
    public Collection $completedModuleIds;
    /** @var Collection<int, Submission> Submissions keyed by task_id */
    public Collection $submissionsMap;

    /** @var Collection<int, \App\Models\UserTaskStart> Task starts keyed by task_id */
    public Collection $taskStartsMap;

    public int $progressPercent = 0;

    public function mount(Course $course): void
    {
        $user = auth()->user();

        // 1. Fetch completed course IDs to verify prerequisites
        $allCoursesWithModules = Course::query()
            ->with(['modules.materials', 'modules.tasks'])
            ->get();

        $this->readMaterialIds = XpLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'material_read')
            ->pluck('reference_id');

        $this->gradedTaskIds = Submission::query()
            ->where('user_id', $user->id)
            ->where('status', 'graded')
            ->pluck('task_id');

        $this->completedCourseIds = $allCoursesWithModules->filter(function (Course $c) use ($user): bool {
            return $c->isCompletedByUser($user, $this->readMaterialIds, $this->gradedTaskIds);
        })->pluck('id');

        // 2. Perform security gate check
        if ($course->isLockedForUser($user, $this->completedCourseIds)) {
            abort(403, 'Akses Ditolak: Kursus ini masih terkunci! Selesaikan persyaratan terlebih dahulu.');
        }
        // 3. Load full modules tree
        $this->course = $course->load(['modules.materials', 'modules.tasks', 'minLevel', 'prerequisite']);

        // 4. Load all submissions for this user to map in-memory
        $taskIds = $course->modules->flatMap->tasks->pluck('id');
        $this->submissionsMap = Submission::query()
            ->where('user_id', $user->id)
            ->whereIn('task_id', $taskIds)
            ->get()
            ->keyBy('task_id');

        // Load task starts for this user to calculate relative deadlines
        $this->taskStartsMap = \App\Models\UserTaskStart::query()
            ->where('user_id', $user->id)
            ->whereIn('task_id', $taskIds)
            ->get()
            ->keyBy('task_id');

        // 5. Identify completed modules
        $this->completedModuleIds = $course->modules->filter(function ($module) use ($user): bool {
            return $module->isCompletedByUser($user, $this->readMaterialIds, $this->gradedTaskIds);
        })->pluck('id');

        // 6. Calculate overall course completion progress percentage
        $totalItems = 0;
        $completedItems = 0;

        foreach ($course->modules as $module) {
            foreach ($module->materials as $mat) {
                $totalItems++;
                if ($this->readMaterialIds->contains($mat->id)) {
                    $completedItems++;
                }
            }
            foreach ($module->tasks as $task) {
                $totalItems++;
                if ($this->gradedTaskIds->contains($task->id)) {
                    $completedItems++;
                }
            }
        }

        if ($totalItems > 0) {
            $this->progressPercent = (int) round(($completedItems / $totalItems) * 100);
        }
    }

    public function render(): View
    {
        return view('livewire.course-show');
    }
}
