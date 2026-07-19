<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\LMS\GradeSubmissionAction;
use App\Events\XpEarned;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class GradeSubmissionActionTest extends TestCase
{
    use RefreshDatabase;

    private GradeSubmissionAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GradeSubmissionAction();
    }

    public function test_grades_submission_and_awards_proportional_xp(): void
    {
        Event::fake();

        $student = User::factory()->create(['total_xp' => 10]);
        $task = Task::factory()->create(['base_xp' => 100]);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'score' => null,
            'status' => 'pending',
        ]);

        // Grade with 85% score
        $earnedXp = $this->action->execute($submission, 85);

        $this->assertEquals(85, $earnedXp);
        $this->assertEquals(85, $submission->fresh()->score);
        $this->assertEquals('graded', $submission->fresh()->status);

        $this->assertEquals(95, $student->fresh()->total_xp); // 10 + 85

        $this->assertDatabaseHas('xp_logs', [
            'user_id' => $student->id,
            'action' => 'task_graded',
            'xp_earned' => 85,
            'reference_id' => $task->id,
        ]);

        Event::assertDispatched(XpEarned::class, function (XpEarned $event) use ($student): bool {
            return $event->user->id === $student->id && $event->xpEarned === 85;
        });
    }
}
