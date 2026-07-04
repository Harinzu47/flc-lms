<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Events\XpEarned;
use App\Listeners\EvaluateBadgeUnlocks;
use App\Models\Badge;
use App\Models\Submission;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EvaluateBadgeUnlocksTest extends TestCase
{
    use RefreshDatabase;

    private EvaluateBadgeUnlocks $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new EvaluateBadgeUnlocks();
    }

    public function test_unlocks_total_xp_badge_when_threshold_is_met(): void
    {
        $user = User::factory()->create(['total_xp' => 500]);
        
        $badge = Badge::factory()->create([
            'criteria_type' => 'total_xp',
            'target_value' => 500,
            'name' => 'XP Master',
        ]);

        $event = new XpEarned($user, 100);

        $this->listener->handle($event);

        $this->assertTrue($user->fresh()->badges->contains($badge->id));

        $this->assertDatabaseHas('pending_celebrations', [
            'user_id' => $user->id,
            'type' => 'badge-unlocked',
            'payload->name' => 'XP Master',
        ]);
    }

    public function test_unlocks_materials_read_badge_when_threshold_is_met(): void
    {
        $user = User::factory()->create(['total_xp' => 10]);

        $badge = Badge::factory()->create([
            'criteria_type' => 'materials_read',
            'target_value' => 3,
            'name' => 'Bookworm',
        ]);

        // Simulate 3 material reads in XpLog
        XpLog::factory()->count(3)->create([
            'user_id' => $user->id,
            'action' => 'material_read',
        ]);

        $event = new XpEarned($user, 10);

        $this->listener->handle($event);

        $this->assertTrue($user->fresh()->badges->contains($badge->id));
    }

    public function test_unlocks_tasks_completed_badge_when_threshold_is_met(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);

        $badge = Badge::factory()->create([
            'criteria_type' => 'tasks_completed',
            'target_value' => 2,
            'name' => 'Assignment Conqueror',
        ]);

        // Simulate 2 completed (graded) tasks
        Submission::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'graded',
        ]);

        $event = new XpEarned($user, 50);

        $this->listener->handle($event);

        $this->assertTrue($user->fresh()->badges->contains($badge->id));
    }
}
