<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched whenever a user earns experience points (XP).
 *
 * This serves as the integration hook for decoupled gamification side-effects
 * such as level synchronization and badge unlock evaluations.
 */
final class XpEarned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user     The user who earned the XP.
     * @param int  $xpEarned The amount of XP earned in this transaction.
     */
    public function __construct(
        public readonly User $user,
        public readonly int  $xpEarned
    ) {}
}
