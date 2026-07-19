<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardDecouplingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_analytics_dashboard_on_dashboard_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        // Access the dashboard and verify we see admin analytics view parameters
        Livewire::test(\App\Livewire\GamifiedDashboard::class)
            ->assertStatus(200)
            ->assertViewHas('totalStudents')
            ->assertViewHas('pendingGradingCount')
            ->assertViewHas('flaggedCount');
            
        // Test tab switching
        Livewire::test(\App\Livewire\GamifiedDashboard::class)
            ->assertSet('activeTab', 'overview')
            ->set('activeTab', 'leaderboard')
            ->assertSet('activeTab', 'leaderboard')
            ->assertViewHas('topStudents');
    }

    public function test_student_receives_gamified_dashboard_on_dashboard_endpoint(): void
    {
        $student = User::factory()->create(['role' => 'member']);

        $this->actingAs($student);

        // Access the dashboard and verify we see student dashboard parameters
        Livewire::test(\App\Livewire\GamifiedDashboard::class)
            ->assertStatus(200)
            ->assertViewHas('user')
            ->assertViewHas('badges')
            ->assertViewHas('xpLogs')
            ->assertViewHas('leaderboard')
            ->assertViewHas('upcomingTasks')
            ->assertViewHas('allLevels');
    }
}
