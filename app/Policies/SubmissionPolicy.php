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
        if ($submission === null) {
            return $user->isInstruktur();
        }

        return $user->id === $submission->user_id || $user->isInstruktur();
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, ?Submission $submission = null): bool
    {
        return $user->isInstruktur();
    }
}
