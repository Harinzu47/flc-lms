<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage courses (and associated curriculum like modules, materials, tasks).
     */
    public function manage(User $user): bool
    {
        return $user->role === 'admin';
    }
}
