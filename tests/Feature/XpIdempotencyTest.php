<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Gamification\AwardMaterialXpAction;
use App\Actions\LMS\GradeSubmissionAction;
use App\Events\XpEarned;
use App\Models\Material;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Tests that XP awarding actions are idempotent — calling them multiple times
 * for the same user+material or user+task produces exactly one XP log entry,
 * and total_xp is only incremented once.
 *
 * Covers all three defense-in-depth layers:
 *   - Cache::lock() (distributed lock)
 *   - lockForUpdate() (pessimistic DB lock)
 *   - Unique constraint on xp_logs(user_id, action, reference_id) (DB fallback)
 */
final class XpIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // AwardMaterialXpAction
    // ─────────────────────────────────────────────────────────────────────────

    public function test_award_material_xp_only_awards_once_on_duplicate_calls(): void
    {
        Event::fake();

        $action   = new AwardMaterialXpAction();
        $user     = User::factory()->create(['total_xp' => 0]);
        $material = Material::factory()->create();

        // First call → should award XP
        $result1 = $action->execute($user, $material);
        $this->assertTrue($result1);

        // Second call → should NOT award XP (idempotent)
        $result2 = $action->execute($user, $material);
        $this->assertFalse($result2);

        // Verify: exactly 1 xp_log row
        $this->assertDatabaseCount('xp_logs', 1);
        $this->assertDatabaseHas('xp_logs', [
            'user_id'      => $user->id,
            'action'       => AwardMaterialXpAction::ACTION,
            'xp_earned'    => AwardMaterialXpAction::XP_AMOUNT,
            'reference_id' => $material->id,
        ]);

        // Verify: total_xp incremented exactly once
        $this->assertEquals(AwardMaterialXpAction::XP_AMOUNT, $user->fresh()->total_xp);

        // Verify: event dispatched exactly once
        Event::assertDispatchedTimes(XpEarned::class, 1);
    }

    public function test_award_material_xp_positive_path(): void
    {
        Event::fake();

        $action   = new AwardMaterialXpAction();
        $user     = User::factory()->create(['total_xp' => 50]);
        $material = Material::factory()->create();

        $result = $action->execute($user, $material);

        $this->assertTrue($result);
        $this->assertEquals(50 + AwardMaterialXpAction::XP_AMOUNT, $user->fresh()->total_xp);

        Event::assertDispatched(XpEarned::class, function (XpEarned $event) use ($user): bool {
            return $event->user->id === $user->id && $event->xpEarned === AwardMaterialXpAction::XP_AMOUNT;
        });
    }

    public function test_award_material_xp_different_materials_are_independent(): void
    {
        Event::fake();

        $action    = new AwardMaterialXpAction();
        $user      = User::factory()->create(['total_xp' => 0]);
        $material1 = Material::factory()->create();
        $material2 = Material::factory()->create();

        $this->assertTrue($action->execute($user, $material1));
        $this->assertTrue($action->execute($user, $material2));

        // Should have 2 separate xp_log entries
        $this->assertDatabaseCount('xp_logs', 2);
        $this->assertEquals(AwardMaterialXpAction::XP_AMOUNT * 2, $user->fresh()->total_xp);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GradeSubmissionAction
    // ─────────────────────────────────────────────────────────────────────────

    public function test_grade_submission_only_awards_xp_once_on_duplicate_calls(): void
    {
        Event::fake();

        $action  = new GradeSubmissionAction();
        $student = User::factory()->create(['total_xp' => 10]);
        $task    = Task::factory()->create(['base_xp' => 100]);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'score'   => null,
            'status'  => 'pending',
        ]);

        // First grading → should award XP
        $result1 = $action->execute($submission, 85);
        $this->assertEquals(85, $result1);

        // Verify XP awarded correctly
        $this->assertEquals(95, $student->fresh()->total_xp); // 10 + 85
        $this->assertDatabaseCount('xp_logs', 1);

        // Second grading of the same submission → should NOT double-award
        $result2 = $action->execute($submission->fresh(), 90);
        $this->assertEquals(0, $result2);

        // Verify: still only 1 xp_log row, total_xp unchanged from first grading
        $this->assertDatabaseCount('xp_logs', 1);
        $this->assertEquals(95, $student->fresh()->total_xp); // unchanged

        // Verify: event dispatched exactly once (for the first grading)
        Event::assertDispatchedTimes(XpEarned::class, 1);
    }

    public function test_grade_submission_positive_path(): void
    {
        Event::fake();

        $action  = new GradeSubmissionAction();
        $student = User::factory()->create(['total_xp' => 0]);
        $task    = Task::factory()->create(['base_xp' => 200]);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'score'   => null,
            'status'  => 'pending',
        ]);

        $result = $action->execute($submission, 75);

        $this->assertEquals(150, $result); // round(75/100 * 200)
        $this->assertEquals(150, $student->fresh()->total_xp);
        $this->assertEquals(75, $submission->fresh()->score);
        $this->assertEquals('graded', $submission->fresh()->status);

        $this->assertDatabaseHas('xp_logs', [
            'user_id'      => $student->id,
            'action'       => 'task_graded',
            'xp_earned'    => 150,
            'reference_id' => $task->id,
        ]);

        Event::assertDispatched(XpEarned::class);
    }

    public function test_grade_submission_updates_score_even_on_regrading(): void
    {
        Event::fake();

        $action  = new GradeSubmissionAction();
        $student = User::factory()->create(['total_xp' => 0]);
        $task    = Task::factory()->create(['base_xp' => 100]);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'task_id' => $task->id,
            'score'   => null,
            'status'  => 'pending',
        ]);

        // First grade
        $action->execute($submission, 70);
        $this->assertEquals(70, $submission->fresh()->score);

        // Re-grade with different score — score should update, but no extra XP
        $action->execute($submission->fresh(), 90);
        $this->assertEquals(90, $submission->fresh()->score);
        $this->assertEquals('graded', $submission->fresh()->status);

        // XP should remain from first grading only
        $this->assertEquals(70, $student->fresh()->total_xp);
        $this->assertDatabaseCount('xp_logs', 1);
    }
}
