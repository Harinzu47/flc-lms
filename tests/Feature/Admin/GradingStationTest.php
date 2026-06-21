<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Events\XpEarned;
use App\Models\Course;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

final class GradingStationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_grading_station(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.grading'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_grading_station(): void
    {
        $student = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($student)->get(route('admin.grading'));

        $response->assertStatus(403);
    }

    public function test_admin_can_grade_submission_and_award_proportional_xp(): void
    {
        Event::fake([XpEarned::class]);

        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member', 'total_xp' => 100]);
        
        $course = Course::create(['title' => 'Test Course', 'difficulty_level' => 'beginner']);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Module 1', 'sort_order' => 1]);
        $task = Task::create([
            'module_id' => $module->id,
            'title' => 'Midterm Essay',
            'description' => 'A midterm assignment.',
            'type' => 'essay',
            'base_xp' => 200,
        ]);

        $submission = Submission::create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'answer_text' => 'My answer',
            'status' => 'pending',
        ]);

        // Score: 85 -> XP earned = round((85 / 100) * 200) = 170 XP
        Livewire::actingAs($admin)
            ->test(\App\Livewire\GradingStation::class)
            ->call('selectSubmission', $submission->id)
            ->set("scoreForm.{$submission->id}", '85')
            ->call('submitGrade')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertEquals(270, $student->fresh()->total_xp);
        
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'score' => 85,
            'status' => 'graded',
        ]);

        $this->assertDatabaseHas('xp_logs', [
            'user_id' => $student->id,
            'action' => 'task_graded',
            'xp_earned' => 170,
            'reference_id' => $task->id,
        ]);

        Event::assertDispatched(XpEarned::class, function (XpEarned $event) use ($student) {
            return $event->user->id === $student->id && $event->xpEarned === 170;
        });
    }

    public function test_admin_can_flag_submission_for_revision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member']);
        
        $course = Course::create(['title' => 'Test Course', 'difficulty_level' => 'beginner']);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Module 1', 'sort_order' => 1]);
        $task = Task::create([
            'module_id' => $module->id,
            'title' => 'Final Essay',
            'description' => 'A final assignment.',
            'type' => 'essay',
            'base_xp' => 100,
        ]);

        $submission = Submission::create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'answer_text' => 'Short answer',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\GradingStation::class)
            ->call('selectSubmission', $submission->id)
            ->set('reviewComment', 'Harap perbaiki tata bahasa Anda.')
            ->call('toggleFlag', $submission->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'is_flagged' => true,
            'review_comment' => 'Harap perbaiki tata bahasa Anda.',
        ]);
    }
}
