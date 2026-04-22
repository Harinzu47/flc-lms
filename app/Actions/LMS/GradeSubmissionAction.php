<?php

declare(strict_types=1);

namespace App\Actions\LMS;

use App\Models\Submission;
use App\Models\XpLog;
use Illuminate\Support\Facades\DB;

/**
 * Grades a submission and atomically awards calculated XP to the student.
 *
 * XP formula: earned = round((score / 100) × task.base_xp)
 * Example: score 85, base_xp 100 → 85 XP awarded.
 */
final class GradeSubmissionAction
{
    /**
     * Execute the grading.
     *
     * @param  Submission $submission The submission to grade (must be loaded with 'task' and 'user').
     * @param  int        $score      A score between 0 and 100.
     * @return int                    The XP amount awarded to the student.
     */
    public function execute(Submission $submission, int $score): int
    {
        // ── Calculate XP proportionally to the score ─────────────────────────
        $earnedXp = (int) round(($score / 100) * $submission->task->base_xp);

        DB::transaction(function () use ($submission, $score, $earnedXp): void {

            // 1. Update the submission record
            $submission->update([
                'score'  => $score,
                'status' => 'graded',
            ]);

            // 2. Write an XP log entry for the student (append-only audit trail)
            XpLog::create([
                'user_id'      => $submission->user_id,
                'action'       => 'task_graded',
                'xp_earned'    => $earnedXp,
                'reference_id' => $submission->task_id,
            ]);

            // 3. Atomically increment the student's XP counter
            $submission->user->increment('total_xp', $earnedXp);
        });

        return $earnedXp;
    }
}
