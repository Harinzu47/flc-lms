<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Course;
use App\Models\Submission;
use App\Models\XpLog;
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

        $enrolledCourseIds = $user->courses()->pluck('courses.id');

        return view('livewire.library', [
            'courses' => $courses,
            'completedCourseIds' => $completedCourseIds,
            'enrolledCourseIds' => $enrolledCourseIds,
        ]);
    }

    /**
     * Enroll the current user into a course (ambil peminatan).
     */
    public function enroll(int $courseId): void
    {
        $course = Course::where('is_published', true)->findOrFail($courseId);
        $user = auth()->user();

        // Security check: Ensure the course isn't locked by level/prerequisite
        if ($course->isLockedForUser($user)) {
            abort(403, 'Akses Ditolak: Anda belum memenuhi syarat untuk mengambil peminatan ini.');
        }

        // Attach user to course (using syncWithoutDetaching to prevent duplicates)
        $user->courses()->syncWithoutDetaching([$course->id]);

        $this->dispatch('notify', message: "Berhasil mengambil peminatan: {$course->title}");
    }
}
