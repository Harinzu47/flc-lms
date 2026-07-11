<?php

declare(strict_types=1);

namespace App\Actions\LMS;

use App\Models\Submission;
use App\Models\XpLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Grades a submission and atomically awards calculated XP to the student.
 *
 * XP formula: earned = round((score / 100) × task.base_xp)
 * Example: score 85, base_xp 100 → 85 XP awarded.
 *
 * Defense-in-depth (3 layers):
 *   1. Cache::lock()       — distributed lock prevents concurrent grading of same submission.
 *   2. lockForUpdate()     — pessimistic DB lock on submission row + idempotency check.
 *   3. Unique constraint   — DB-level unique(user_id, action, reference_id) as final fallback.
 */
final class GradeSubmissionAction
{
    /**
     * Execute the grading.
     *
     * @param  Submission $submission The submission to grade (must be loaded with 'task' and 'user').
     * @param  int        $score      A score between 0 and 100.
     * @return int                    The XP amount awarded to the student (0 if already graded).
     */
    public function execute(Submission $submission, int $score): int
    {
        // ── Calculate XP proportionally to the score ─────────────────────────
        $earnedXp = (int) round(($score / 100) * $submission->task->base_xp);

        // Layer 1: Distributed lock — prevents concurrent grading of the same submission
        $lock = Cache::lock("xp-award:grading:{$submission->id}", 5);

        if (! $lock->get()) {
            return 0;
        }

        try {
            return $this->gradeAndAwardXp($submission, $score, $earnedXp);
        } finally {
            $lock->release();
        }
    }

    /**
     * Core grading + XP award logic wrapped in a transaction with pessimistic locking.
     */
    private function gradeAndAwardXp(Submission $submission, int $score, int $earnedXp): int
    {
        $xpAwarded = 0;

        try {
            // Layer 2: Pessimistic lock + transaction
            DB::transaction(function () use ($submission, $score, $earnedXp, &$xpAwarded): void {
                // Lock the submission row to serialize concurrent grading attempts
                $lockedSubmission = Submission::query()
                    ->where('id', $submission->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedSubmission === null) {
                    return;
                }

                // 1. Update the submission record
                $lockedSubmission->update([
                    'score'  => $score,
                    'status' => 'graded',
                ]);

                // Idempotency guard: skip XP award if already granted for this task
                $alreadyAwarded = XpLog::query()
                    ->where('user_id',      $submission->user_id)
                    ->where('action',       'task_graded')
                    ->where('reference_id', $submission->task_id)
                    ->exists();

                if (!$alreadyAwarded) {
                    // 2. Write an XP log entry for the student (append-only audit trail)
                    XpLog::create([
                        'user_id'      => $submission->user_id,
                        'action'       => 'task_graded',
                        'xp_earned'    => $earnedXp,
                        'reference_id' => $submission->task_id,
                    ]);

                    // 3. Atomically increment the student's XP counter
                    $submission->user->increment('total_xp', $earnedXp);
                    $xpAwarded = $earnedXp;
                }
            });
        } catch (QueryException $e) {
            // Layer 3: Unique constraint violation (SQLSTATE 23000) — already awarded
            if ($e->getCode() === '23000') {
                // Still update the submission score/status even on constraint violation
                $submission->update([
                    'score'  => $score,
                    'status' => 'graded',
                ]);

                return 0;
            }

            throw $e;
        }

        if ($xpAwarded > 0) {
            // Dispatch decoupled event for level/badge sync for the graded student
            \App\Events\XpEarned::dispatch($submission->user, $xpAwarded);
        }

        return $xpAwarded;
    }
}
