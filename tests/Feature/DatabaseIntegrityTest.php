<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\UserManager;
use App\Models\Course;
use App\Models\Module;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    // 1. Soft Deletes
    public function test_soft_deleted_user_does_not_appear_in_normal_queries()
    {
        $user = User::factory()->create(['role' => 'peserta']);
        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_soft_delete_course_with_children_succeeds()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $task = Task::factory()->create(['module_id' => $module->id]);
        $user = User::factory()->create();
        $submission = Submission::factory()->create(['task_id' => $task->id, 'user_id' => $user->id]);

        $course->delete();

        $this->assertNotNull(Course::withTrashed()->find($course->id));
        $this->assertNotNull($course->deleted_at);
        $this->assertNotNull(Module::withTrashed()->find($module->id)->deleted_at);
        $this->assertNotNull(Task::withTrashed()->find($task->id)->deleted_at);
    }

    public function test_force_delete_course_with_children_fails_with_restrict_violation()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/Cannot delete or update a parent row: a foreign key constraint fails|FOREIGN KEY constraint failed/');
        
        $course->forceDelete();
    }

    public function test_soft_deleted_user_cannot_login()
    {
        $password = 'password123';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user->delete();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_soft_deleted_submission_still_triggers_unique_constraint()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $task = Task::factory()->create(['module_id' => $module->id]);
        
        $submission = Submission::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        $submission->delete();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/Duplicate entry|UNIQUE constraint failed/');

        Submission::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
    }

    // 2. XP Floor Validation
    public function test_adjust_xp_rejects_negative_total()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['total_xp' => 50]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->set('userId', $user->id)
            ->set('xpDelta', -60)
            ->set('xpReason', 'Penalty for cheating')
            ->call('adjustXp')
            ->assertHasErrors(['xpDelta']);

        $this->assertEquals(50, $user->fresh()->total_xp);
    }

    public function test_adjust_xp_allows_exactly_zero_total()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['total_xp' => 50]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->set('userId', $user->id)
            ->set('xpDelta', -50)
            ->set('xpReason', 'Complete reset')
            ->call('adjustXp')
            ->assertHasNoErrors(['xpDelta']);

        $this->assertEquals(0, $user->fresh()->total_xp);
    }

    public function test_adjust_xp_works_normally_for_positive()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['total_xp' => 50]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->set('userId', $user->id)
            ->set('xpDelta', 30)
            ->set('xpReason', 'Bonus XP')
            ->call('adjustXp')
            ->assertHasNoErrors(['xpDelta']);

        $this->assertEquals(80, $user->fresh()->total_xp);
    }
}
