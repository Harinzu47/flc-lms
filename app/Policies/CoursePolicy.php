<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage courses (and associated curriculum like modules, materials, tasks).
     */
    public function manage(User $user, ?Course $course = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isInstruktur()) {
            return false;
        }

        if ($course === null) {
            return true;
        }

        return $course->isEnrolledByUser($user);
    }
}
