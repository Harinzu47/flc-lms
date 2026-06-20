<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'answer_text',
        'file_url',
        'score',
        'status',
        'is_flagged',
        'review_comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_flagged' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * A submission belongs to a task.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * A submission belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get a clean, human-readable name for the submitted file.
     */
    public function getFriendlyFileNameAttribute(): string
    {
        if (!$this->file_url) {
            return '';
        }

        $extension = pathinfo($this->file_url, PATHINFO_EXTENSION);
        
        $userName = $this->user ? $this->user->name : 'Mahasiswa';
        $taskTitle = $this->task ? $this->task->title : 'Tugas';

        // Clean special characters and replace spaces with underscores
        $studentNameClean = preg_replace('/[^a-zA-Z0-9]/', '_', $userName);
        $taskTitleClean = preg_replace('/[^a-zA-Z0-9]/', '_', $taskTitle);

        // Remove duplicate underscores and clean borders
        $studentNameClean = trim(preg_replace('/_+/', '_', $studentNameClean), '_');
        $taskTitleClean = trim(preg_replace('/_+/', '_', $taskTitleClean), '_');

        return "Tugas_{$studentNameClean}_{$taskTitleClean}.{$extension}";
    }
}
