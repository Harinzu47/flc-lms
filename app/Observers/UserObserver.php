<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

final class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        if ($user->wasRecentlyCreated || $user->wasChanged(['total_xp', 'name', 'email', 'role', 'level_id'])) {
            $this->invalidateLeaderboard();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->invalidateLeaderboard();
    }

    /**
     * Invalidate cached leaderboard keys.
     */
    private function invalidateLeaderboard(): void
    {
        cache()->forget('leaderboard.top.5');
        cache()->forget('leaderboard.top.10');
    }
}
