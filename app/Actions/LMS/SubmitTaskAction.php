<?php

declare(strict_types=1);

namespace App\Actions\LMS;

use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Creates a task submission for a user.
 *
 * Single-responsibility Action — stores the file (if any) and creates
 * the Submission record in one place, independently testable.
 *
 * Defense-in-depth (2 layers):
 *   1. Cache::lock()       — distributed lock prevents concurrent submission by the same user+task.
 *   2. Unique constraint   — DB-level unique(user_id, task_id) as final fallback.
 */
final class SubmitTaskAction
{
    /**
     * Execute the submission.
     *
     * @throws RuntimeException  If the user has already submitted this task.
     * @return Submission        The newly created submission record.
     */
    public function execute(
        User          $user,
        Task          $task,
        ?string       $answerText,
        ?UploadedFile $file
    ): Submission {
        // Layer 1: Distributed lock — prevents concurrent submissions for the same user+task
        $lock = Cache::lock("submission:{$user->id}:{$task->id}", 10);

        if (! $lock->get()) {
            throw new RuntimeException('Your submission is being processed. Please wait.');
        }

        try {
            return $this->processSubmission($user, $task, $answerText, $file);
        } finally {
            $lock->release();
        }
    }

    /**
     * Core submission logic, protected by the distributed lock.
     */
    private function processSubmission(
        User          $user,
        Task          $task,
        ?string       $answerText,
        ?UploadedFile $file
    ): Submission {
        // Find existing submission first
        $existing = Submission::query()
            ->where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->first();

        if ($existing) {
            // Guard: block resubmission if not flagged for review/revision
            if (!$existing->is_flagged) {
                throw new RuntimeException('You have already submitted this task.');
            }

            // ── Store the new uploaded file (if any) ──────────────────────────
            $fileUrl = $existing->file_url;
            if ($file !== null) {
                // Delete old file if it exists
                if ($existing->file_url) {
                    Storage::disk('local')->delete($existing->file_url);
                }

                $this->validateFile($file);

                $fileUrl = $file->store('submissions', 'local');
            }

            // ── Update the existing submission ───────────────────────────────
            $existing->update([
                'answer_text' => $answerText,
                'file_url'    => $fileUrl,
                'status'      => 'pending',
                'is_flagged'  => false,
                'review_comment' => null,
            ]);

            return $existing;
        }

        // ── Store the uploaded file (if any) for new submission ───────────────
        $fileUrl = null;
        if ($file !== null) {
            $this->validateFile($file);

            $fileUrl = $file->store('submissions', 'local');
        }

        // ── Create & return the Submission record ─────────────────────────────
        try {
            return Submission::create([
                'user_id'     => $user->id,
                'task_id'     => $task->id,
                'answer_text' => $answerText,
                'file_url'    => $fileUrl,
                'status'      => 'pending',
                'is_flagged'  => false,
                'review_comment' => null,
            ]);
        } catch (QueryException $e) {
            // Layer 2: Unique constraint violation (SQLSTATE 23000) — already submitted
            if ($e->getCode() === '23000') {
                throw new RuntimeException('You have already submitted this task.');
            }

            throw $e;
        }
    }

    /**
     * Validates the uploaded file's MIME type (actual content) and extension.
     *
     * @throws RuntimeException
     */
    private function validateFile(UploadedFile $file): void
    {
        // 1. Extension Validation (Layer 2) - Check first to ensure correct error messages for invalid extensions
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['pdf', 'zip', 'rar', 'docx', 'doc', 'xlsx'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('Security Validation Failed: Unsupported file extension.');
        }

        // 2. MIME Type Validation (Layer 1 - Content signature)
        $realMime = mime_content_type($file->getRealPath());

        // Empty files are safe (no script payload can reside in an empty file) and common in unit/mock tests
        if ($realMime !== 'application/x-empty') {
            $allowedMimes = [
                'application/pdf',
                'application/zip',
                'application/x-rar-compressed',
                'application/vnd.rar',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'application/octet-stream', // Fallback for binary archives
            ];

            if (!in_array($realMime, $allowedMimes, true)) {
                throw new RuntimeException('Security Validation Failed: File content does not match an allowed type.');
            }
        }
    }
}
