<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Badge;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.base')]
#[Title('Badge Management — FLC Admin')]
class BadgeManager extends Component
{
    use WithPagination;

    // ── UI & Modal States ────────────────────────────────────────────────────
    public bool $isModalOpen = false;
    public ?int $selectedBadgeId = null;

    // ── Form fields with validation rules ────────────────────────────────────
    #[Validate('required|string|min:3|max:100')]
    public string $name = '';

    #[Validate('required|string|min:10|max:255')]
    public string $description = '';

    #[Validate('required|string|max:10')]
    public string $icon = '🏅';

    #[Validate('required|in:total_xp,materials_read,tasks_completed')]
    public string $criteriaType = 'total_xp';

    #[Validate('required|integer|min:1')]
    public int $targetValue = 1;

    // ── Component Lifecycle ──────────────────────────────────────────────────
    public function render(): View
    {
        $badges = Badge::orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.admin.badge-manager', compact('badges'));
    }

    // ── CRUD Methods ─────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->resetForm();
        $this->openModal();
    }

    public function edit(Badge $badge): void
    {
        $this->resetForm();
        $this->selectedBadgeId = $badge->id;
        $this->name            = $badge->name;
        $this->description     = $badge->description;
        $this->icon            = $badge->icon;
        $this->criteriaType    = $badge->criteria_type;
        $this->targetValue     = (int) $badge->target_value;
        $this->openModal();
    }

    public function saveBadge(): void
    {
        $validated = $this->validate();

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated): void {
            Badge::updateOrCreate(
                ['id' => $this->selectedBadgeId],
                [
                    'name'          => $validated['name'],
                    'description'   => $validated['description'],
                    'icon'          => $validated['icon'],
                    'criteria_type' => $validated['criteriaType'],
                    'target_value'  => $validated['targetValue'],
                ]
            );
        });

        $this->closeModal();
        $this->dispatch('notify', message: $this->selectedBadgeId ? 'Badge updated successfully.' : 'Badge created successfully.');
    }

    public function deleteBadge(int $id): void
    {
        $badge = Badge::findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($badge): void {
            // Detach users first to satisfy cascade safety
            $badge->users()->detach();
            $badge->delete();
        });

        $this->dispatch('notify', message: 'Badge deleted successfully.');
    }

    // ── Modal State Guards ───────────────────────────────────────────────────
    public function openModal(): void
    {
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->selectedBadgeId = null;
        $this->name            = '';
        $this->description     = '';
        $this->icon            = '🏅';
        $this->criteriaType    = 'total_xp';
        $this->targetValue     = 1;
        $this->resetValidation();
    }
}
