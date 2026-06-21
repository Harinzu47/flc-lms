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

        // 1. Eager load minLevel, prerequisite, and modules with items to check completion in-memory
        $courses = Course::query()
            ->with(['minLevel', 'prerequisite', 'modules.materials', 'modules.tasks'])
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

        // Filter already loaded collection in-memory (prevents double-fetching memory bloat)
        $completedCourseIds = $courses->filter(function (Course $c) use ($user, $readMaterialIds, $gradedTaskIds): bool {
            return $c->isCompletedByUser($user, $readMaterialIds, $gradedTaskIds);
        })->pluck('id');

        return view('livewire.library', [
            'courses'            => $courses,
            'completedCourseIds' => $completedCourseIds,
        ]);
    }
}
