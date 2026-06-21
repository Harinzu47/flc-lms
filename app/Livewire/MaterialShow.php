<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Gamification\AwardMaterialXpAction;
use App\Models\Material;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Full-page Livewire component for the Material Detail screen.
 *
 * Responsibilities (thin component):
 *   - Route model binding for the Material.
 *   - Deriving initial UI state ($hasRead) on mount.
 *   - Delegating XP business logic entirely to AwardMaterialXpAction.
 *   - Dispatching a browser event to trigger the Alpine.js toast notification.
 */
#[Layout('layouts.app')]
#[Title('Material Detail — FLC LMS')]
class MaterialShow extends Component
{
    /** Injected via route model binding: /materials/{material} */
    public Material $material;

    /** Tracks whether the authenticated user has already read this material. */
    public bool $hasRead = false;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount(Material $material): void
    {
        $this->material = $material;
        $user = auth()->user();

        // Prevent N+1 queries by pre-fetching completed course, module, material, and task collections
        $this->material->load(['module.course.modules.materials', 'module.course.modules.tasks']);

        $readMaterialIds = \App\Models\XpLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'material_read')
            ->pluck('reference_id');

        $gradedTaskIds = \App\Models\Submission::query()
            ->where('user_id', $user->id)
            ->where('status', 'graded')
            ->pluck('task_id');

        $allCoursesWithModules = \App\Models\Course::query()
            ->with(['modules.materials', 'modules.tasks'])
            ->get();

        $completedCourseIds = $allCoursesWithModules->filter(function (\App\Models\Course $c) use ($user, $readMaterialIds, $gradedTaskIds): bool {
            return $c->isCompletedByUser($user, $readMaterialIds, $gradedTaskIds);
        })->pluck('id');

        $completedModuleIds = collect();
        if ($this->material->module && $this->material->module->course) {
            foreach ($this->material->module->course->modules as $mod) {
                if ($mod->isCompletedByUser($user, $readMaterialIds, $gradedTaskIds)) {
                    $completedModuleIds->push($mod->id);
                }
            }
        }

        // Secure back-door progression gate: Abort if the material is locked for the authenticated user
        if ($material->isLockedForUser($user, $completedModuleIds, $completedCourseIds)) {
            abort(403, 'Materi ini masih terkunci! Tingkatkan level Anda terlebih dahulu.');
        }

        // Efficient single EXISTS query — no XpLog collection loaded into memory.
        $this->hasRead = $material->xpLogs()
            ->where('user_id', auth()->id())
            ->exists();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    /**
     * Mark the material as read and attempt to award XP.
     *
     * Laravel's service container injects AwardMaterialXpAction automatically.
     * This method is intentionally <10 lines — all logic lives in the Action.
     */
    public function markAsRead(AwardMaterialXpAction $action): void
    {

        $awarded = $action->execute(auth()->user(), $this->material);

        if ($awarded) {
            $this->hasRead = true;

            // Dispatch a browser event; Alpine.js listens via @notify.window
            $this->dispatch('notify', message: 'XP Awarded! +10 XP');
        }
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render(): \Illuminate\View\View
    {
        return view('livewire.material-show');
    }
}
