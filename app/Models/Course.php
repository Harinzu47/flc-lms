<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'difficulty_level',
        'min_level_required',
        'prerequisite_course_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    /**
     * All users attached to this course (any role).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Peserta (students) enrolled in this course.
     */
    public function peserta(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->where('users.role', User::ROLE_PESERTA)
            ->withTimestamps();
    }

    /**
     * Instruktur (teachers) assigned to this course.
     */
    public function instruktur(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->where('users.role', User::ROLE_INSTRUKTUR)
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::deleting(function (Course $course) {
            $course->modules->each(fn ($m) => $m->delete());
        });
    }

    public function minLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'min_level_required');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    // ── Gating & Progression Helpers ──────────────────────────────────────────

    /**
     * Determine if this course is locked for the user.
     * Evaluates in-memory if a collection of completed course IDs is provided.
     *
     * @param  Collection<int>|null  $completedCourseIds
     */
    public function isLockedForUser(User $user, ?Collection $completedCourseIds = null): bool
    {
        // 1. Check Level Gating
        if ($this->min_level_required !== null) {
            $requiredLevel = $this->minLevel;
            if ($requiredLevel !== null && (int) ($user->total_xp ?? 0) < (int) $requiredLevel->min_xp) {
                return true;
            }
        }

        // 2. Check Prerequisite Gating
        if ($this->prerequisite_course_id !== null) {
            if ($completedCourseIds !== null) {
                return ! $completedCourseIds->contains($this->prerequisite_course_id);
            }

            // Database query fallback
            $prereq = $this->prerequisite;
            if ($prereq !== null && ! $prereq->isCompletedByUser($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user is explicitly enrolled in or assigned to this course.
     */
    public function isEnrolledByUser(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine if the user has completed this course (all materials read, all tasks completed/graded).
     *
     * @param  Collection<int>|null  $readMaterialIds
     * @param  Collection<int>|null  $gradedTaskIds
     */
    public function isCompletedByUser(User $user, ?Collection $readMaterialIds = null, ?Collection $gradedTaskIds = null): bool
    {
        $modules = $this->modules;

        if ($modules->isEmpty()) {
            return false;
        }

        foreach ($modules as $module) {
            if (! $module->isCompletedByUser($user, $readMaterialIds, $gradedTaskIds)) {
                return false;
            }
        }

        return true;
    }
}
