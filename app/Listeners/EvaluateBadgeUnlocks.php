<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Gamification\AwardMaterialXpAction;
use App\Events\XpEarned;
use App\Models\Badge;
use App\Models\Submission;
use App\Models\XpLog;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener to dynamically evaluate and unlock badges for a student.
 *
 * Runs asynchronously via background queue processing.
 */
final class EvaluateBadgeUnlocks implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(XpEarned $event): void
    {
        $user = $event->user;

        // 1. Pre-calculate metrics to avoid N+1 query locks inside the loop
        $userXp = $user->total_xp;
        $totalRead = \App\Models\XpLog::where('user_id', $user->id)->where('action', 'material_read')->count();
        $totalTasks = \App\Models\Submission::where('user_id', $user->id)->where('status', 'graded')->count();

        // 2. Fetch available locked badges
        $availableBadges = Badge::whereDoesntHave('users', function ($query) use ($user): void {
            $query->where('user_id', $user->id);
        })->get();

        // 3. Loop and evaluate conditions using pre-calculated variables
        foreach ($availableBadges as $badge) {
            $shouldUnlock = false;

            switch ($badge->criteria_type) {
                case 'total_xp':
                    $shouldUnlock = ($userXp >= $badge->target_value);
                    break;

                case 'materials_read':
                    $shouldUnlock = ($totalRead >= $badge->target_value);
                    break;

                case 'tasks_completed':
                    $shouldUnlock = ($totalTasks >= $badge->target_value);
                    break;
            }

            // 4. Safely attach unlocked badge
            if ($shouldUnlock) {
                $user->badges()->syncWithoutDetaching([
                    $badge->id => ['unlocked_at' => now()],
                ]);
            }
        }
    }
}
