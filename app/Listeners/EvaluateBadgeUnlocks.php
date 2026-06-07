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

        // Fetch only badges that this specific user has not unlocked yet to avoid wasteful queries
        $lockedBadges = Badge::whereDoesntHave('users', function ($query) use ($user): void {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($lockedBadges as $badge) {
            $shouldUnlock = false;

            switch ($badge->criteria_type) {
                case 'total_xp':
                    $shouldUnlock = $user->total_xp >= $badge->criteria_value;
                    break;

                case 'materials_read':
                    $readCount = XpLog::query()
                        ->where('user_id', $user->id)
                        ->where('action', AwardMaterialXpAction::ACTION)
                        ->count();
                    $shouldUnlock = $readCount >= $badge->criteria_value;
                    break;

                case 'tasks_completed':
                    $completedCount = Submission::query()
                        ->where('user_id', $user->id)
                        ->where('status', 'graded')
                        ->count();
                    $shouldUnlock = $completedCount >= $badge->criteria_value;
                    break;
            }

            if ($shouldUnlock) {
                // Attach badge to user pivot relationship
                $user->badges()->syncWithoutDetaching([
                    $badge->id => ['unlocked_at' => now()],
                ]);
            }
        }
    }
}
