<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class SubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, ?Submission $submission = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($submission === null) {
            return $user->isInstruktur();
        }

        if ($user->id === $submission->user_id) {
            return true;
        }

        if ($user->isInstruktur()) {
            $course = $submission->task?->module?->course;

            return $course !== null && $course->isEnrolledByUser($user);
        }

        return false;
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, ?Submission $submission = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isInstruktur()) {
            return false;
        }

        if ($submission === null) {
            return true;
        }

        $course = $submission->task?->module?->course;

        return $course !== null && $course->isEnrolledByUser($user);
    }
}
