<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Level;

final class LevelObserver
{
    /**
     * Handle the Level "saved" event.
     */
    public function saved(Level $level): void
    {
        cache()->forget('levels.all');
    }

    /**
     * Handle the Level "deleted" event.
     */
    public function deleted(Level $level): void
    {
        cache()->forget('levels.all');
    }
}
