<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'description',
        'type',
        'base_xp',
        'days_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_limit' => 'integer',
            'base_xp'  => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The module this task belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * A task has many submissions from users.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * A task has many user starts.
     */
    public function userStarts(): HasMany
    {
        return $this->hasMany(UserTaskStart::class);
    }

    protected static function booted()
    {
        static::deleting(function (Task $task) {
            XpLog::where('action', 'task_graded')->where('reference_id', $task->id)->delete();
            $task->submissions()->delete();
        });
    }

    /**
     * Get the computed personal deadline for a given user.
     */
    public function getPersonalDeadlineFor(User $user): ?\Carbon\Carbon
    {
        if ($this->days_limit === null) {
            return null;
        }
        $start = $this->userStarts->where('user_id', $user->id)->first();
        return $start ? $start->started_at->copy()->addDays($this->days_limit) : null;
    }

    /**
     * Get upcoming tasks with a future deadline for a given user.
     *
     * ⚡ Perf: Pushes filtering into SQL to avoid loading all tasks into memory.
     * Only fetches tasks with a days_limit, that the user has started, and that
     * don't have a non-flagged submission.
     */
    public static function getUpcomingForUser(User $user, int $limit = 3): Collection
    {
        return self::query()
            ->whereNotNull('days_limit')
            ->whereHas('userStarts', fn ($q) => $q->where('user_id', $user->id))
            ->with(['userStarts' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }, 'submissions' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->filter(function (Task $task) use ($user) {
                // Check if an associated submission exists
                $submission = $task->submissions->where('user_id', $user->id)->first();

                // Exclude tasks where a submission exists, UNLESS that submission is flagged (revisi)
                if ($submission && !$submission->is_flagged) {
                    return false;
                }

                // Check if a start record exists and deadline is in the future
                $deadline = $task->getPersonalDeadlineFor($user);
                if (!$deadline) {
                    return false;
                }

                // Keep only if computed personal deadline is in the future (> now())
                return $deadline->isFuture();
            })
            ->map(function (Task $task) use ($user) {
                // Dynamically assign legacy 'deadline' property as a Carbon instance for view compatibility
                $task->deadline = $task->getPersonalDeadlineFor($user);
                return $task;
            })
            ->sortBy(function (Task $task) use ($user) {
                return $task->deadline;
            })
            ->take($limit);
    }

    // Gamification Helpers
    // -------------------------------------------------------------------------

    /**
     * Determine if this task is locked for the user based on course & module gates.
     */
    public function isLockedForUser(
        User $user,
        ?Collection $completedModuleIds = null,
        ?Collection $completedCourseIds = null
    ): bool {
        $module = $this->module;
        if ($module === null) {
            return false;
        }

        $course = $module->course;
        if ($course !== null && $course->isLockedForUser($user, $completedCourseIds)) {
            return true;
        }

        return $module->isLockedForUser($user, $completedModuleIds);
    }
}
