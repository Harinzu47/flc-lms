<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Material extends Model
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
        'file_url',
        'type',
        'xp_reward',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The module this material belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * XP log entries that reference this material as a reward source.
     * Used to efficiently check whether a user has already earned XP for reading.
     */
    public function xpLogs(): HasMany
    {
        return $this->hasMany(XpLog::class, 'reference_id');
    }

    // -------------------------------------------------------------------------
    // Gamification Helpers
    // -------------------------------------------------------------------------

    /**
     * Determine if this material is locked for the given user based on course & module gates.
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
