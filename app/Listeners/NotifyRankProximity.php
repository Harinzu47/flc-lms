<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\XpEarned;
use App\Models\PendingCelebration;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener that notifies a member when they are approaching the rank of the
 * member directly above them on the leaderboard.
 *
 * Runs asynchronously via background queue processing, consistent with
 * SyncUserLevel and EvaluateBadgeUnlocks.
 *
 * Design decisions:
 *  - Payload intentionally omits the above-user's name/email to avoid
 *    negative social comparison (Festinger, 1954; Hanus & Fox, 2015).
 *  - Anti-spam: only fires when the gap drops significantly compared to
 *    the last notification (configurable via gamification.rank_proximity_min_gap_change).
 */
final class NotifyRankProximity implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(XpEarned $event): void
    {
        $user = $event->user;

        // Only members participate in the leaderboard ranking.
        if ($user->role !== 'member') {
            return;
        }

        $threshold    = (int) config('gamification.rank_proximity_threshold', 50);
        $minGapChange = (int) config('gamification.rank_proximity_min_gap_change', 10);

        // Find the member directly above this user on the leaderboard.
        // Uses scalar query — never loads the full collection.
        // Strict ">" means tied users are NOT considered "above" each other.
        $aboveUserXp = User::query()
            ->where('role', 'member')
            ->where('total_xp', '>', $user->total_xp)
            ->orderBy('total_xp')
            ->value('total_xp');

        // User is already #1 — nobody above them.
        if ($aboveUserXp === null) {
            return;
        }

        $gap = (int) $aboveUserXp - (int) $user->total_xp;

        // Gap exceeds threshold — reset anti-spam tracker so the user can be
        // notified again if they re-enter the proximity zone later.
        if ($gap > $threshold) {
            if ($user->last_rank_gap_notified !== null) {
                $user->last_rank_gap_notified = null;
                $user->save();
            }

            return;
        }

        // ── Anti-spam gate ──────────────────────────────────────────────────
        if ($user->last_rank_gap_notified !== null) {
            $improvement = (int) $user->last_rank_gap_notified - $gap;

            if ($improvement < $minGapChange) {
                // Gap hasn't decreased enough since the last notification.
                return;
            }
        }

        // Calculate the rank this user would achieve if they surpassed the
        // member above them.  Uses the same COUNT-based pattern proven in
        // HallOfFame.php — rank = (members with more XP) + 1, then minus 1
        // because we want the rank they'd reach by overtaking one person.
        $currentRank = User::query()
            ->where('role', 'member')
            ->where('total_xp', '>', $user->total_xp)
            ->count() + 1;

        $targetRank = $currentRank - 1;

        // ── Duplicate guard ─────────────────────────────────────────────────
        // Primary: exists() check handles the normal (non-concurrent) duplicate case.
        // Secondary: try/catch handles the concurrent TOCTOU edge case where two
        // queue workers both pass the exists() check simultaneously.
        $alreadyPending = PendingCelebration::query()
            ->where('user_id', $user->id)
            ->where('type', 'rank_progress')
            ->exists();

        if (! $alreadyPending) {
            try {
                PendingCelebration::create([
                    'user_id' => $user->id,
                    'type'    => 'rank_progress',
                    'payload' => [
                        'xp_gap'      => $gap,
                        'target_rank' => $targetRank,
                    ],
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Duplicate insert from concurrent worker — safe to ignore.
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        // Update anti-spam tracker.
        $user->last_rank_gap_notified = $gap;
        $user->save();
    }
}
