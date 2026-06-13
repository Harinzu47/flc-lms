<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Course;
use App\Models\XpLog;
use App\Models\Submission;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Full-page Livewire component for the Student Library / Courses Catalog.
 */
#[Layout('layouts.gamified')]
#[Title('Library — FLC LMS')]
class Library extends Component
{
    public function render(): View
    {
        $user = auth()->user();

        // 1. Eager load minLevel and prerequisite for courses in the list
        $courses = Course::query()
            ->with(['minLevel', 'prerequisite'])
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        // 2. Mitigate N+1 queries by fetching student reads and grades in single queries
        $readMaterialIds = XpLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'material_read')
            ->pluck('reference_id');

        $gradedTaskIds = Submission::query()
            ->where('user_id', $user->id)
            ->where('status', 'graded')
            ->pluck('task_id');

        // Eager load nested modules structure for all courses to check completion in-memory
        $allCoursesWithModules = Course::query()
            ->with(['modules.materials', 'modules.tasks'])
            ->get();

        $completedCourseIds = $allCoursesWithModules->filter(function (Course $c) use ($user, $readMaterialIds, $gradedTaskIds): bool {
            return $c->isCompletedByUser($user, $readMaterialIds, $gradedTaskIds);
        })->pluck('id');

        return view('livewire.library', [
            'courses'            => $courses,
            'completedCourseIds' => $completedCourseIds,
        ]);
    }
}
