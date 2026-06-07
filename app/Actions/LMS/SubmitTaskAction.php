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
        // ── Guard: one submission per user per task ───────────────────────────
        $alreadySubmitted = Submission::query()
            ->where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->exists();

        if ($alreadySubmitted) {
            throw new RuntimeException('You have already submitted this task.');
        }

        // ── Store the uploaded file (if any) ──────────────────────────────────
        $fileUrl = null;
        if ($file !== null) {
            // Defense in depth: Double check extension before storing
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['pdf', 'zip', 'rar', 'docx', 'doc', 'xlsx'];
            
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new RuntimeException('Security Validation Failed: Unsupported file extension.');
            }

            // Stored in storage/app/public/submissions.
            // store() automatically generates a cryptographically secure randomized hash
            // (e.g. submissions/8ab3d9...zip) to prevent guessing and path traversal attacks.
            $fileUrl = $file->store('submissions', 'public');
        }

        // ── Create & return the Submission record ─────────────────────────────
        return Submission::create([
            'user_id'     => $user->id,
            'task_id'     => $task->id,
            'answer_text' => $answerText,
            'file_url'    => $fileUrl,
            'status'      => 'pending',
        ]);
    }
}
