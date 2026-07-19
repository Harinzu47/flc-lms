<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SubmissionDownloadController extends Controller
{
    /**
     * Download the submission file securely.
     */
    public function download(Submission $submission): StreamedResponse
    {
        // Authorize download using SubmissionPolicy
        Gate::authorize('view', $submission);

        // Defense-in-depth: Prevent path traversal attacks
        if (str_contains($submission->file_url, '..')) {
            abort(400, 'Invalid file path.');
        }

        if (!$submission->file_url || !Storage::disk('local')->exists($submission->file_url)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($submission->file_url);
    }
}
