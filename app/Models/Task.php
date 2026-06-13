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
        'deadline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
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

    // -------------------------------------------------------------------------
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
