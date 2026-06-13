<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\XpEarned;
use App\Models\Badge;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.users'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $student = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($student)->get(route('admin.users'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserManager::class)
            ->set('name', 'New Student')
            ->set('email', 'newstudent@example.com')
            ->set('role', 'member')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'role' => 'member',
            'total_xp' => 0,
        ]);
    }

    public function test_admin_can_adjust_user_xp_and_trigger_event(): void
    {
        Event::fake([XpEarned::class]);

        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member', 'total_xp' => 100]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserManager::class)
            ->set('userId', $student->id)
            ->set('xpDelta', 250)
            ->set('xpReason', 'Bonus Tugas Tambahan')
            ->call('adjustXp')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertEquals(350, $student->fresh()->total_xp);

        $this->assertDatabaseHas('xp_logs', [
            'user_id' => $student->id,
            'action' => 'Manual Correction: Bonus Tugas Tambahan',
            'xp_earned' => 250,
            'reference_id' => $admin->id,
        ]);

        Event::assertDispatched(XpEarned::class, function (XpEarned $event) use ($student) {
            return $event->user->id === $student->id && $event->xpEarned === 250;
        });
    }

    public function test_sync_badges_preserves_old_timestamps(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member']);
        $badge1 = Badge::create([
            'name' => 'Badge One',
            'description' => 'Desc 1',
            'icon_url' => 'badge1.png',
            'criteria_type' => 'total_xp',
            'criteria_value' => 100,
        ]);
        $badge2 = Badge::create([
            'name' => 'Badge Two',
            'description' => 'Desc 2',
            'icon_url' => 'badge2.png',
            'criteria_type' => 'total_xp',
            'criteria_value' => 200,
        ]);

        // Unlocked 3 days ago
        $unlockedAt = now()->subDays(3);
        $student->badges()->attach($badge1->id, ['unlocked_at' => $unlockedAt]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\UserManager::class)
            ->set('userId', $student->id)
            ->set('selectedBadges', [(string) $badge1->id, (string) $badge2->id])
            ->call('syncBadges')
            ->assertHasNoErrors();

        // Verify that badge1 retains its 3 days ago timestamp
        $this->assertEquals(
            $unlockedAt->toDateTimeString(),
            $student->fresh()->badges()->where('badge_id', $badge1->id)->first()->pivot->unlocked_at
        );

        // Verify that badge2 is awarded with a fresh timestamp
        $badge2UnlockedAt = $student->fresh()->badges()->where('badge_id', $badge2->id)->first()->pivot->unlocked_at;
        $this->assertNotNull($badge2UnlockedAt);
        $this->assertTrue(now()->diffInSeconds(new \DateTime($badge2UnlockedAt)) < 5);
    }
}
