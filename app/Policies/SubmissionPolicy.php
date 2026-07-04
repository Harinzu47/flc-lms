<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Submission;
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
            return $user->role === 'admin';
        }
        return $user->id === $submission->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, ?Submission $submission = null): bool
    {
        return $user->role === 'admin';
    }
}
