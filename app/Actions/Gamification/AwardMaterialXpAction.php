<?php

declare(strict_types=1);

namespace App\Actions\Gamification;

use App\Models\Material;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Support\Facades\DB;

/**
 * Awards +10 XP to a user for reading a material.
 *
 * Idempotent — calling this multiple times for the same user+material is safe;
 * subsequent calls after the first are silently ignored and return false.
 *
 * Design: Single-responsibility Action class, keeping the Livewire component
 * thin and this logic independently testable.
 */
final class AwardMaterialXpAction
{
    public const XP_AMOUNT = 10;
    public const ACTION    = 'material_read';

    /**
     * Execute the action.
     *
     * @return bool  true  → XP was awarded (first time reading this material)
     *               false → XP was NOT awarded (already claimed, idempotent guard)
     */
    public function execute(User $user, Material $material): bool
    {
        // ── Guard: already claimed? ──────────────────────────────────────────
        $alreadyClaimed = XpLog::query()
            ->where('user_id',      $user->id)
            ->where('action',       self::ACTION)
            ->where('reference_id', $material->id)
            ->exists();

        if ($alreadyClaimed) {
            return false;
        }

        // ── Atomic: log entry + user counter update ──────────────────────────
        DB::transaction(function () use ($user, $material): void {
            XpLog::create([
                'user_id'      => $user->id,
                'action'       => self::ACTION,
                'xp_earned'    => self::XP_AMOUNT,
                'reference_id' => $material->id,
            ]);

            // Use increment() for an atomic UPDATE — avoids lost-update race conditions
            // from read-modify-write cycles (e.g. $user->total_xp += 10; $user->save()).
            $user->increment('total_xp', self::XP_AMOUNT);
        });

        return true;
    }
}
