<?php

declare(strict_types=1);

namespace App\Actions\LMS;

use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Creates a task submission for a user.
 *
 * Single-responsibility Action — stores the file (if any) and creates
 * the Submission record in one place, independently testable.
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

                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'zip', 'rar', 'docx', 'doc', 'xlsx'];
                
                if (!in_array($extension, $allowedExtensions, true)) {
                    throw new RuntimeException('Security Validation Failed: Unsupported file extension.');
                }

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
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['pdf', 'zip', 'rar', 'docx', 'doc', 'xlsx'];
            
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new RuntimeException('Security Validation Failed: Unsupported file extension.');
            }

            $fileUrl = $file->store('submissions', 'local');
        }

        // ── Create & return the Submission record ─────────────────────────────
        return Submission::create([
            'user_id'     => $user->id,
            'task_id'     => $task->id,
            'answer_text' => $answerText,
            'file_url'    => $fileUrl,
            'status'      => 'pending',
            'is_flagged'  => false,
            'review_comment' => null,
        ]);
    }
}
