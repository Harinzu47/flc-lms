<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTaskStart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RelativeDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_access_registers_start_time_safely(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $course = Course::create([
            'title' => 'Test Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'sort_order' => 1,
        ]);

        $task = Task::create([
            'module_id' => $module->id,
            'title' => 'Test Task',
            'description' => 'Test task description',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);

        $this->actingAs($user);

        // Verify no start record exists
        $this->assertDatabaseMissing('user_task_starts', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        // Access the TaskShow Livewire component
        Livewire::test(\App\Livewire\TaskShow::class, ['task' => $task])
            ->assertStatus(200);

        // Verify start record has been registered
        $this->assertDatabaseHas('user_task_starts', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        $start = UserTaskStart::where('user_id', $user->id)->where('task_id', $task->id)->first();
        $this->assertNotNull($start);
        $this->assertNotNull($start->started_at);
    }

    public function test_student_subsequent_access_does_not_modify_start_time(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $course = Course::create([
            'title' => 'Test Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'sort_order' => 1,
        ]);

        $task = Task::create([
            'module_id' => $module->id,
            'title' => 'Test Task',
            'description' => 'Test task description',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);

        $this->actingAs($user);

        // Register initial start time in past
        $originalStart = now()->subDays(2)->startOfSecond();
        UserTaskStart::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => $originalStart,
        ]);

        // Access again
        Livewire::test(\App\Livewire\TaskShow::class, ['task' => $task])
            ->assertStatus(200);

        // Verify started_at remains the same
        $currentStart = UserTaskStart::where('user_id', $user->id)->where('task_id', $task->id)->first();
        $this->assertEquals($originalStart->toDateTimeString(), $currentStart->started_at->toDateTimeString());
    }

    public function test_personal_deadline_calculated_correctly(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $course = Course::create([
            'title' => 'Test Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'sort_order' => 1,
        ]);

        $task = Task::create([
            'module_id' => $module->id,
            'title' => 'Test Task',
            'description' => 'Test task description',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);

        $start = UserTaskStart::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => now(),
        ]);

        $deadline = $task->getPersonalDeadlineFor($user);
        $this->assertNotNull($deadline);
        $this->assertTrue($start->started_at->copy()->addDays(5)->eq($deadline));
    }

    public function test_upcoming_tasks_on_dashboard_excludes_submitted_tasks_unless_flagged(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $course = Course::create([
            'title' => 'Test Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'sort_order' => 1,
        ]);

        // Task A: Started, days_limit=5, no submission -> should appear
        $taskA = Task::create([
            'module_id' => $module->id,
            'title' => 'Task A',
            'description' => 'Desc A',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);
        UserTaskStart::create([
            'user_id' => $user->id,
            'task_id' => $taskA->id,
            'started_at' => now(),
        ]);

        // Task B: Started, days_limit=5, has non-flagged submission -> should NOT appear
        $taskB = Task::create([
            'module_id' => $module->id,
            'title' => 'Task B',
            'description' => 'Desc B',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);
        UserTaskStart::create([
            'user_id' => $user->id,
            'task_id' => $taskB->id,
            'started_at' => now(),
        ]);
        Submission::create([
            'user_id' => $user->id,
            'task_id' => $taskB->id,
            'answer_text' => 'B submitted',
            'status' => 'pending',
            'is_flagged' => false,
        ]);

        // Task C: Started, days_limit=5, has FLAGGED submission (needs revision) -> should appear
        $taskC = Task::create([
            'module_id' => $module->id,
            'title' => 'Task C',
            'description' => 'Desc C',
            'type' => 'essay',
            'base_xp' => 100,
            'days_limit' => 5,
        ]);
        UserTaskStart::create([
            'user_id' => $user->id,
            'task_id' => $taskC->id,
            'started_at' => now(),
        ]);
        Submission::create([
            'user_id' => $user->id,
            'task_id' => $taskC->id,
            'answer_text' => 'C submitted',
            'status' => 'pending',
            'is_flagged' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\GamifiedDashboard::class)
            ->assertStatus(200)
            ->assertViewHas('upcomingTasks', function ($tasks) use ($taskA, $taskB, $taskC) {
                return $tasks->contains('id', $taskA->id)
                    && !$tasks->contains('id', $taskB->id)
                    && $tasks->contains('id', $taskC->id);
            });
    }
}
