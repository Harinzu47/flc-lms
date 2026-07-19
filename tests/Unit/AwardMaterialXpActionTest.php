<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Gamification\AwardMaterialXpAction;
use App\Events\XpEarned;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class AwardMaterialXpActionTest extends TestCase
{
    use RefreshDatabase;

    private AwardMaterialXpAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AwardMaterialXpAction();
    }

    public function test_awards_xp_and_dispatches_event_on_first_read(): void
    {
        Event::fake();

        $user = User::factory()->create(['total_xp' => 0]);
        $material = Material::factory()->create();

        $result = $this->action->execute($user, $material);

        $this->assertTrue($result);
        $this->assertEquals(AwardMaterialXpAction::XP_AMOUNT, $user->fresh()->total_xp);
        
        $this->assertDatabaseHas('xp_logs', [
            'user_id' => $user->id,
            'action' => AwardMaterialXpAction::ACTION,
            'xp_earned' => AwardMaterialXpAction::XP_AMOUNT,
            'reference_id' => $material->id,
        ]);

        Event::assertDispatched(XpEarned::class, function (XpEarned $event) use ($user): bool {
            return $event->user->id === $user->id && $event->xpEarned === AwardMaterialXpAction::XP_AMOUNT;
        });
    }

    public function test_does_not_award_xp_on_subsequent_reads_idempotency(): void
    {
        Event::fake();

        $user = User::factory()->create(['total_xp' => 10]);
        $material = Material::factory()->create();

        // Simulate first read
        \App\Models\XpLog::create([
            'user_id' => $user->id,
            'action' => AwardMaterialXpAction::ACTION,
            'xp_earned' => AwardMaterialXpAction::XP_AMOUNT,
            'reference_id' => $material->id,
        ]);

        $result = $this->action->execute($user, $material);

        $this->assertFalse($result);
        $this->assertEquals(10, $user->fresh()->total_xp); // Unchanged
        
        // Assert no new event was dispatched
        Event::assertNotDispatched(XpEarned::class);
    }
}
