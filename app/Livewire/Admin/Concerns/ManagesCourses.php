<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Concerns;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

trait ManagesCourses
{
    public function createCourse(): void
    {
        Gate::authorize('manage', Course::class);
        $this->resetCourseForm();
        $this->isCourseModalOpen = true;
    }

    public function editCourse(Course $course): void
    {
        Gate::authorize('manage', Course::class);
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
        Gate::authorize('manage', Course::class);
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
        Gate::authorize('manage', Course::class);
        DB::transaction(function () use ($course): void {
            $course->delete();
        });

        $this->dispatch('notify', message: 'Course and all its contents deleted successfully.');
        $this->deselectCourse();
    }

    public function closeCourseModal(): void
    {
        $this->isCourseModalOpen = false;
        $this->resetCourseForm();
    }

    protected function resetCourseForm(): void
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
}
