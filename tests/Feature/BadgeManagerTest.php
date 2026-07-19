<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BadgeManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_badge_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.badges'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_badge_management(): void
    {
        $student = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($student)->get(route('admin.badges'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\BadgeManager::class)
            ->set('name', 'Grammar Wizard')
            ->set('description', 'Completed three English tasks.')
            ->set('icon', '🧙')
            ->set('criteriaType', 'tasks_completed')
            ->set('targetValue', 3)
            ->call('saveBadge')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('badges', [
            'name' => 'Grammar Wizard',
            'description' => 'Completed three English tasks.',
            'icon' => '🧙',
            'criteria_type' => 'tasks_completed',
            'target_value' => 3,
        ]);
    }

    public function test_admin_can_edit_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $badge = Badge::create([
            'name' => 'Old Badge',
            'description' => 'Old Description of badge',
            'icon' => '🏅',
            'criteria_type' => 'total_xp',
            'target_value' => 100,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\BadgeManager::class)
            ->call('edit', $badge)
            ->assertSet('name', 'Old Badge')
            ->set('name', 'Updated Badge')
            ->call('saveBadge')
            ->assertHasNoErrors();

        $this->assertEquals('Updated Badge', $badge->fresh()->name);
    }

    public function test_admin_can_delete_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $badge = Badge::create([
            'name' => 'To Be Deleted',
            'description' => 'Will be removed soon.',
            'icon' => '🗑️',
            'criteria_type' => 'total_xp',
            'target_value' => 500,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\BadgeManager::class)
            ->call('deleteBadge', $badge->id)
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('badges', [
            'id' => $badge->id,
        ]);
    }
}
