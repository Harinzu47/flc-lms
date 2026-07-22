<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CelebrationHub;
use App\Models\PendingCelebration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RankProgressCelebrationTest extends TestCase
{
    use RefreshDatabase;

    // ─── 1. rank_progress celebration renders in CelebrationHub ────────────

    public function test_rank_progress_celebration_renders_with_progress_message(): void
    {
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        PendingCelebration::create([
            'user_id' => $user->id,
            'type'    => 'rank_progress',
            'payload' => ['xp_gap' => 15, 'target_rank' => 4],
        ]);

        $component = Livewire::actingAs($user)
            ->test(CelebrationHub::class);

        // The celebrations array should contain our rank_progress entry
        $celebrations = $component->get('celebrations');

        $this->assertNotEmpty($celebrations);

        $rankProgress = collect($celebrations)->firstWhere('type', 'rank_progress');
        $this->assertNotNull($rankProgress, 'rank_progress celebration should be present in celebrations array');
        $this->assertEquals(15, $rankProgress['payload']['xp_gap']);
        $this->assertEquals(4, $rankProgress['payload']['target_rank']);
    }

    // ─── 2. PendingCelebration deleted after mount (consumed) ──────────────

    public function test_rank_progress_pending_celebration_is_deleted_after_render(): void
    {
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        PendingCelebration::create([
            'user_id' => $user->id,
            'type'    => 'rank_progress',
            'payload' => ['xp_gap' => 15, 'target_rank' => 4],
        ]);

        $this->assertDatabaseHas('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);

        Livewire::actingAs($user)->test(CelebrationHub::class);

        // After CelebrationHub::mount() the record should be consumed
        $this->assertDatabaseMissing('pending_celebrations', [
            'user_id' => $user->id,
            'type'    => 'rank_progress',
        ]);
    }

    // ─── 3. No user identity in rendered output ───────────────────────────

    public function test_no_user_identity_appears_in_celebration_hub_output(): void
    {
        $otherUser = User::factory()->create([
            'role'     => 'member',
            'total_xp' => 200,
            'name'     => 'TopSecretStudent',
            'email'    => 'topsecret@example.com',
        ]);

        $user = User::factory()->create(['role' => 'member', 'total_xp' => 185]);

        PendingCelebration::create([
            'user_id' => $user->id,
            'type'    => 'rank_progress',
            'payload' => ['xp_gap' => 15, 'target_rank' => 1],
        ]);

        $component = Livewire::actingAs($user)->test(CelebrationHub::class);

        $html = $component->html();

        $this->assertStringNotContainsString('TopSecretStudent', $html);
        $this->assertStringNotContainsString('topsecret@example.com', $html);
    }

    // ─── 4. Blade renders progress-oriented message text ──────────────────

    public function test_celebration_hub_renders_rank_progress_as_notify_dispatch(): void
    {
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        PendingCelebration::create([
            'user_id' => $user->id,
            'type'    => 'rank_progress',
            'payload' => ['xp_gap' => 25, 'target_rank' => 3],
        ]);

        $component = Livewire::actingAs($user)->test(CelebrationHub::class);

        $html = $component->html();

        // The Alpine template should contain the notify dispatch with rank_progress handling
        $this->assertStringContainsString('rank_progress', $html);
        // The message template references xp_gap and target_rank
        $this->assertStringContainsString('XP lagi untuk naik ke peringkat', $html);
    }
}
