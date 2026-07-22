<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\XpEarned;
use App\Models\Level;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener to automatically synchronize the user's current level in the database.
 *
 * By implementing ShouldQueue, this runs asynchronously via background queues,
 * removing the level-up check from the HTTP request thread.
 */
final class SyncUserLevel implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(XpEarned $event): void
    {
        $user = $event->user;

        // Query the highest level this user qualifies for based on their current total_xp
        $newLevel = Level::query()
            ->where('min_xp', '<=', $user->total_xp)
            ->orderByDesc('min_xp')
            ->first();

        // If a valid level matches and it differs from the cached level_id, sync it atomically
        if ($newLevel !== null && $user->level_id !== $newLevel->id) {
            $oldLevelId = $user->level_id;
            $user->level_id = $newLevel->id;
            $user->save();

            if ($oldLevelId !== null || $newLevel->min_xp > 0) {
                // Note: since min_xp is the threshold field, we pass min_xp
                $this->dispatch($user->id, $newLevel->name, (int) $newLevel->min_xp);
            }
        }
    }

    /**
     * Dispatch event to browser (via database).
     * Guards against duplicate celebrations for the same level.
     */
    private function dispatch(int $userId, string $levelName, int $targetXp): void
    {
        // Primary: exists() check handles the normal (non-concurrent) duplicate case.
        $alreadyPending = \App\Models\PendingCelebration::query()
            ->where('user_id', $userId)
            ->where('type', 'level-up')
            ->whereJsonContains('payload->levelName', $levelName)
            ->exists();

        if ($alreadyPending) {
            return;
        }

        // Secondary: try/catch handles the concurrent TOCTOU edge case where two
        // queue workers both pass the exists() check simultaneously.
        try {
            \App\Models\PendingCelebration::create([
                'user_id' => $userId,
                'type'    => 'level-up',
                'payload' => [
                    'levelName' => $levelName,
                    'targetXp'  => $targetXp,
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate insert from concurrent worker — safe to ignore.
            if ($e->getCode() !== '23000') {
                throw $e;
            }
        }
    }
}
