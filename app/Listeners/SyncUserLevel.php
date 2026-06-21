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
            $user->update([
                'level_id' => $newLevel->id,
            ]);

            if ($oldLevelId !== null || $newLevel->min_xp > 0) {
                $this->dispatch($user->id, 'level-up', levelName: $newLevel->name, targetXp: $newLevel->xp_threshold);
            }
        }
    }

    /**
     * Dispatch event to browser (via database).
     */
    private function dispatch(int $userId, string $event, ...$payload): void
    {
        if ($event === 'level-up') {
            \App\Models\PendingCelebration::create([
                'user_id' => $userId,
                'type'    => 'level-up',
                'payload' => [
                    'levelName' => $payload['levelName'] ?? '',
                    'targetXp'  => $payload['targetXp'] ?? 0,
                ],
            ]);
        }
    }
}
