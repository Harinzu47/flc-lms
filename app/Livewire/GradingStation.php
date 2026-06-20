<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\LMS\GradeSubmissionAction;
use App\Models\Submission;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Full-page Livewire component for the Admin Grading Station.
 *
 * Stitch AI Screen ID: 4674a34a60d7456dbdd5c27972191b11
 *
 * Architecture:
 *  - $pendingSubmissions is kept as a Collection in memory; graded items are
 *    removed client-side (no full reload needed) giving a snappy SPA feel.
 *  - $scoreForm is a keyed array [submission_id => score_value] so multiple
 *    scores can be staged without collisions between list items.
 *  - GradeSubmissionAction is method-injected (service container).
 */
#[Layout('layouts.base')]
#[Title('Grading Station — FLC Admin')]
class GradingStation extends Component
{
    // ── State ─────────────────────────────────────────────────────────────────

    /** All pending submissions (eager-loaded), keyed by id for fast lookup. */
    public Collection $pendingSubmissions;

    /** The submission currently open in the grading panel. */
    public ?Submission $selectedSubmission = null;

    /**
     * Score staging area: [submission_id => score_string_from_input]
     * Using an array allows wire:model="scoreForm.{{ $id }}" per row.
     *
     * @var array<int|string, string>
     */
    public array $scoreForm = [];

    /** Feedback/review comment to provide when flagging a submission. */
    public string $reviewComment = '';

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->loadPendingSubmissions();
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Open a submission in the grading panel.
     */
    public function selectSubmission(int $id): void
    {
        // Resolve from the in-memory collection first (avoids an extra query
        // when the user clicks between items).
        $this->selectedSubmission = $this->pendingSubmissions->firstWhere('id', $id);

        if ($this->selectedSubmission) {
            $this->reviewComment = $this->selectedSubmission->review_comment ?? '';
        } else {
            $this->reviewComment = '';
        }
    }

    /**
     * Toggle the flagged status of a submission.
     */
    public function toggleFlag(int $id): void
    {
        $submission = Submission::findOrFail($id);

        if (! $submission->is_flagged) {
            // Validate that reviewComment is required, string, and of appropriate length
            $this->validate([
                'reviewComment' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'reviewComment.required' => 'Wajib memberikan catatan revisi jika menandai tugas.',
                'reviewComment.min' => 'Catatan revisi minimal harus terdiri dari 5 karakter.',
                'reviewComment.max' => 'Catatan revisi maksimal 1000 karakter.',
            ]);

            $submission->update([
                'is_flagged' => true,
                'review_comment' => $this->reviewComment,
            ]);
        } else {
            // Clear state by setting is_flagged to false and review_comment to null
            $submission->update([
                'is_flagged' => false,
                'review_comment' => null,
            ]);
            $this->reviewComment = '';
        }

        if ($this->selectedSubmission && $this->selectedSubmission->id === $id) {
            $this->selectedSubmission->refresh();
        }

        $this->loadPendingSubmissions();
    }

    /**
     * Validate the staged score and submit the grade via the Action class.
     *
     * GradeSubmissionAction is injected by Laravel's service container.
     */
    public function submitGrade(GradeSubmissionAction $action): void
    {
        if ($this->selectedSubmission === null) {
            return;
        }

        $submissionId = $this->selectedSubmission->id;

        // ── Validate the score field for this specific submission ──────────────
        $this->validate([
            "scoreForm.{$submissionId}" => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            "scoreForm.{$submissionId}.required" => 'Please enter a score.',
            "scoreForm.{$submissionId}.integer"  => 'Score must be a whole number.',
            "scoreForm.{$submissionId}.min"      => 'Score cannot be below 0.',
            "scoreForm.{$submissionId}.max"      => 'Score cannot exceed 100.',
        ]);

        // ── Execute the grading action ─────────────────────────────────────────
        $earnedXp = $action->execute(
            $this->selectedSubmission,
            (int) $this->scoreForm[$submissionId]
        );

        // ── Update in-memory state (no page reload) ────────────────────────────
        // Remove the graded submission from the list immediately.
        $this->pendingSubmissions = $this->pendingSubmissions
            ->reject(fn (Submission $s) => $s->id === $submissionId);

        // Clear the score field and deselect.
        unset($this->scoreForm[$submissionId]);
        $this->selectedSubmission = null;

        $this->dispatch('notify', message: "Graded! Student awarded {$earnedXp} XP.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function loadPendingSubmissions(): void
    {
        $this->pendingSubmissions = Submission::query()
            ->where('status', 'pending')
            ->with(['user', 'task'])      // Eager-load: avoids N+1 in the sidebar list
            ->orderByDesc('is_flagged')
            ->orderBy('created_at')
            ->get();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        return view('livewire.grading-station');
    }
}
