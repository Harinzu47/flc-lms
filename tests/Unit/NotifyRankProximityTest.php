<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Events\XpEarned;
use App\Listeners\NotifyRankProximity;
use App\Models\PendingCelebration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotifyRankProximityTest extends TestCase
{
    use RefreshDatabase;

    private NotifyRankProximity $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new NotifyRankProximity();
    }

    // ─── 1. Gap ≤ threshold → celebration created ──────────────────────────

    public function test_creates_celebration_when_gap_is_within_threshold(): void
    {
        config([
            'gamification.rank_proximity_threshold'      => 50,
            'gamification.rank_proximity_min_gap_change'  => 10,
        ]);

        $aboveUser = User::factory()->create(['role' => 'member', 'total_xp' => 120]);
        $user      = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        $this->listener->handle(new XpEarned($user, 10));

        $this->assertDatabaseHas('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);

        $celebration = PendingCelebration::where('user_id', $user->id)
            ->where('type', 'rank_progress')
            ->first();

        $this->assertNotNull($celebration);
        $this->assertEquals(20, $celebration->payload['xp_gap']);
        // aboveUser is the only person above → current rank is 2, target is 1
        $this->assertEquals(1, $celebration->payload['target_rank']);

        // Anti-spam tracker should be updated
        $this->assertEquals(20, $user->fresh()->last_rank_gap_notified);
    }

    // ─── 2. Gap > threshold → no celebration ───────────────────────────────

    public function test_does_not_create_celebration_when_gap_exceeds_threshold(): void
    {
        config(['gamification.rank_proximity_threshold' => 50]);

        User::factory()->create(['role' => 'member', 'total_xp' => 200]);
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        $this->listener->handle(new XpEarned($user, 10));

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── 3. User is rank #1 → no celebration, no error ─────────────────────

    public function test_no_celebration_when_user_is_already_rank_one(): void
    {
        config(['gamification.rank_proximity_threshold' => 50]);

        $user = User::factory()->create(['role' => 'member', 'total_xp' => 500]);

        // No other member exists with more XP
        $this->listener->handle(new XpEarned($user, 10));

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── 4. Anti-spam: gap hasn't decreased enough → no new celebration ────

    public function test_anti_spam_blocks_notification_when_gap_change_is_insufficient(): void
    {
        config([
            'gamification.rank_proximity_threshold'      => 50,
            'gamification.rank_proximity_min_gap_change'  => 10,
        ]);

        User::factory()->create(['role' => 'member', 'total_xp' => 130]);
        $user = User::factory()->create([
            'role'                    => 'member',
            'total_xp'               => 100,
            'last_rank_gap_notified'  => 35, // Was notified when gap was 35
        ]);

        // Current gap = 30, improvement = 35 - 30 = 5, which is < 10
        $this->listener->handle(new XpEarned($user, 5));

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── 5. Gap dropped significantly → new celebration + tracker updated ──

    public function test_creates_celebration_when_gap_drops_significantly(): void
    {
        config([
            'gamification.rank_proximity_threshold'      => 50,
            'gamification.rank_proximity_min_gap_change'  => 10,
        ]);

        User::factory()->create(['role' => 'member', 'total_xp' => 130]);
        $user = User::factory()->create([
            'role'                    => 'member',
            'total_xp'               => 110,
            'last_rank_gap_notified'  => 35, // Was notified when gap was 35
        ]);

        // Current gap = 20, improvement = 35 - 20 = 15, which is ≥ 10
        $this->listener->handle(new XpEarned($user, 15));

        $this->assertDatabaseHas('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);

        $this->assertEquals(20, $user->fresh()->last_rank_gap_notified);
    }

    // ─── 6. Gap expands beyond threshold → tracker reset to null ───────────

    public function test_resets_tracker_when_gap_exceeds_threshold_again(): void
    {
        config(['gamification.rank_proximity_threshold' => 50]);

        User::factory()->create(['role' => 'member', 'total_xp' => 200]);
        $user = User::factory()->create([
            'role'                    => 'member',
            'total_xp'               => 100,
            'last_rank_gap_notified'  => 40,
        ]);

        // Gap = 100, which exceeds threshold of 50
        $this->listener->handle(new XpEarned($user, 5));

        $this->assertNull($user->fresh()->last_rank_gap_notified);

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── 7. Tied XP → no error, no notification ───────────────────────────

    public function test_tied_xp_does_not_trigger_notification(): void
    {
        config(['gamification.rank_proximity_threshold' => 50]);

        // Both users have the same total_xp — strict ">" means neither is "above"
        User::factory()->create(['role' => 'member', 'total_xp' => 100]);
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        $this->listener->handle(new XpEarned($user, 10));

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── Edge: admin users are skipped ─────────────────────────────────────

    public function test_skips_admin_users(): void
    {
        config(['gamification.rank_proximity_threshold' => 50]);

        User::factory()->create(['role' => 'member', 'total_xp' => 120]);
        $admin = User::factory()->create(['role' => 'admin', 'total_xp' => 100]);

        $this->listener->handle(new XpEarned($admin, 10));

        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $admin->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── Edge: duplicate guard prevents second PendingCelebration ──────────

    public function test_does_not_create_duplicate_pending_celebration(): void
    {
        config([
            'gamification.rank_proximity_threshold'      => 50,
            'gamification.rank_proximity_min_gap_change'  => 10,
        ]);

        User::factory()->create(['role' => 'member', 'total_xp' => 120]);
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        // Pre-existing pending celebration for this user
        PendingCelebration::create([
            'user_id' => $user->id,
            'type'    => 'rank_progress',
            'payload' => ['xp_gap' => 30, 'target_rank' => 1],
        ]);

        $this->listener->handle(new XpEarned($user, 10));

        // Should still be exactly 1 record (the pre-existing one)
        $this->assertCount(
            1,
            PendingCelebration::where('user_id', $user->id)
                ->where('type', 'rank_progress')
                ->get()
        );
    }

    // ─── Payload security: no user identity exposed ────────────────────────

    public function test_payload_does_not_contain_user_identity(): void
    {
        config([
            'gamification.rank_proximity_threshold'      => 50,
            'gamification.rank_proximity_min_gap_change'  => 10,
        ]);

        $aboveUser = User::factory()->create([
            'role'     => 'member',
            'total_xp' => 120,
            'name'     => 'Secret Person',
            'email'    => 'secret@example.com',
        ]);
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        $this->listener->handle(new XpEarned($user, 10));

        $celebration = PendingCelebration::where('user_id', $user->id)
            ->where('type', 'rank_progress')
            ->first();

        $this->assertNotNull($celebration);

        $payloadJson = json_encode($celebration->payload);
        $this->assertStringNotContainsString('Secret Person', $payloadJson);
        $this->assertStringNotContainsString('secret@example.com', $payloadJson);
        $this->assertArrayNotHasKey('name', $celebration->payload);
        $this->assertArrayNotHasKey('email', $celebration->payload);

        // Only expected keys
        $this->assertArrayHasKey('xp_gap', $celebration->payload);
        $this->assertArrayHasKey('target_rank', $celebration->payload);
        $this->assertCount(2, $celebration->payload);
    }
}
