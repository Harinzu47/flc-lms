<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SubmissionDownloadController extends Controller
{
    /**
     * Download the submission file securely.
     */
    public function download(Submission $submission): StreamedResponse
    {
        // Otentikasi & Otorisasi check
        if (auth()->id() !== $submission->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Anda tidak berwenang mengunduh file ini.');
        }

        if (!$submission->file_url || !Storage::disk('local')->exists($submission->file_url)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($submission->file_url);
    }
}
