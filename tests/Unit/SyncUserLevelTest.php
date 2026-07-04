<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Events\XpEarned;
use App\Listeners\SyncUserLevel;
use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SyncUserLevelTest extends TestCase
{
    use RefreshDatabase;

    private SyncUserLevel $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new SyncUserLevel();
    }

    public function test_syncs_user_level_up_when_xp_threshold_is_met(): void
    {
        // 1. Seed levels
        $level1 = Level::factory()->create(['name' => 'Novice', 'min_xp' => 0]);
        $level2 = Level::factory()->create(['name' => 'Apprentice', 'min_xp' => 100]);

        $user = User::factory()->create([
            'level_id' => $level1->id,
            'total_xp' => 120, // Meets level 2 threshold
        ]);

        $event = new XpEarned($user, 120);

        // 2. Call handler
        $this->listener->handle($event);

        $this->assertEquals($level2->id, $user->fresh()->level_id);

        // 3. Assert PendingCelebration created for level-up
        $this->assertDatabaseHas('pending_celebrations', [
            'user_id' => $user->id,
            'type' => 'level-up',
        ]);
    }

    public function test_does_not_level_up_if_xp_is_insufficient(): void
    {
        $level1 = Level::factory()->create(['name' => 'Novice', 'min_xp' => 0]);
        $level2 = Level::factory()->create(['name' => 'Apprentice', 'min_xp' => 100]);

        $user = User::factory()->create([
            'level_id' => $level1->id,
            'total_xp' => 50, // Insufficient for Apprentice
        ]);

        $event = new XpEarned($user, 50);

        $this->listener->handle($event);

        $this->assertEquals($level1->id, $user->fresh()->level_id); // Unchanged

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type' => 'level-up',
        ]);
    }
}
