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
        $xpAwarded = false;

        // Wrap both check and update inside a transaction with pessimistic locking
        DB::transaction(function () use ($user, $material, &$xpAwarded): void {
            // Lock the user record for update to serialize concurrent XP awards for the same user
            $lockedUser = User::query()->where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser !== null) {
                $alreadyClaimed = XpLog::query()
                    ->where('user_id',      $lockedUser->id)
                    ->where('action',       self::ACTION)
                    ->where('reference_id', $material->id)
                    ->exists();

                if (!$alreadyClaimed) {
                    XpLog::create([
                        'user_id'      => $lockedUser->id,
                        'action'       => self::ACTION,
                        'xp_earned'    => self::XP_AMOUNT,
                        'reference_id' => $material->id,
                    ]);

                    $lockedUser->increment('total_xp', self::XP_AMOUNT);
                    $xpAwarded = true;
                }
            }
        });

        if ($xpAwarded) {
            // Dispatch decoupled event for level/badge sync
            \App\Events\XpEarned::dispatch($user, self::XP_AMOUNT);
            return true;
        }

        return false;
    }
}
