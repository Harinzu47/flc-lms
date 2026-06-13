<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'sort_order',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // ── Gating & Progression Helpers ──────────────────────────────────────────

    /**
     * Determine if this module is locked for the user (depends on prior modules being completed).
     *
     * @param User $user
     * @param Collection<int>|null $completedModuleIds
     * @return bool
     */
    public function isLockedForUser(User $user, ?Collection $completedModuleIds = null): bool
    {
        // 1. Fetch parent course modules to identify prior modules in-memory
        $courseModules = $this->course ? $this->course->modules : collect();

        $priorModules = $courseModules->filter(function (Module $m): bool {
            return $m->sort_order < $this->sort_order || ($m->sort_order === $this->sort_order && $m->id < $this->id);
        });

        foreach ($priorModules as $pm) {
            if ($completedModuleIds !== null) {
                if (!$completedModuleIds->contains($pm->id)) {
                    return true;
                }
            } else {
                if (!$pm->isCompletedByUser($user)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the user has completed all materials and tasks in this module.
     *
     * @param User $user
     * @param Collection<int>|null $readMaterialIds
     * @param Collection<int>|null $gradedTaskIds
     * @return bool
     */
    public function isCompletedByUser(User $user, ?Collection $readMaterialIds = null, ?Collection $gradedTaskIds = null): bool
    {
        // Check Materials
        $materials = $this->materials;
        foreach ($materials as $material) {
            if ($readMaterialIds !== null) {
                if (!$readMaterialIds->contains($material->id)) {
                    return false;
                }
            } else {
                $hasRead = XpLog::query()
                    ->where('user_id', $user->id)
                    ->where('action', 'material_read')
                    ->where('reference_id', $material->id)
                    ->exists();

                if (!$hasRead) {
                    return false;
                }
            }
        }

        // Check Tasks
        $tasks = $this->tasks;
        foreach ($tasks as $task) {
            if ($gradedTaskIds !== null) {
                if (!$gradedTaskIds->contains($task->id)) {
                    return false;
                }
            } else {
                $isGraded = Submission::query()
                    ->where('user_id', $user->id)
                    ->where('task_id', $task->id)
                    ->where('status', 'graded')
                    ->exists();

                if (!$isGraded) {
                    return false;
                }
            }
        }

        return true;
    }
}
