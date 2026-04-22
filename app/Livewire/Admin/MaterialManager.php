<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Material;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin Material Manager — full CRUD for the materials catalogue.
 *
 * Architecture:
 *  - Form state is kept as flat public properties (Livewire 3 style).
 *  - #[Validate] attributes on each form property replace the old validate()
 *    array call in save(). validate() is still called explicitly to trigger
 *    the check at the right moment.
 *  - Modal open/close state is owned by Livewire ($isModalOpen) and bridged
 *    to Alpine via @entangle in the view — a single source of truth.
 *  - Pagination is handled by WithPagination; the page resets to 1 whenever
 *    the modal opens so the user always sees fresh results after saving.
 */
#[Layout('layouts.base')]
#[Title('Material Manager — FLC Admin')]
class MaterialManager extends Component
{
    use WithPagination;

    // ── UI State ───────────────────────────────────────────────────────────────

    /** Controls the create/edit modal visibility. */
    public bool $isModalOpen = false;

    /** Set to the Material's id when editing; null when creating. */
    public ?int $editId = null;

    // ── Form Properties (validated via #[Validate]) ────────────────────────────

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|url|max:500')]
    public string $file_url = '';

    #[Validate('required|in:video,document,link')]
    public string $type = 'document';

    #[Validate('required|integer|min:0|max:9999')]
    public int $xp_reward = 10;

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function render(): View
    {
        /** @var LengthAwarePaginator<Material> $materials */
        $materials = Material::latest()->paginate(10);

        return view('livewire.material-manager', compact('materials'));
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Open the modal in "create" mode: clear all form state.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    /**
     * Open the modal pre-filled with the given Material's data.
     */
    public function edit(Material $material): void
    {
        $this->resetForm();
        $this->editId      = $material->id;
        $this->title       = $material->title;
        $this->description = $material->description ?? '';
        $this->file_url    = $material->file_url ?? '';
        $this->type        = $material->type;
        $this->xp_reward   = $material->xp_reward;
        $this->isModalOpen = true;
    }

    /**
     * Validate and persist the form — creates or updates depending on $editId.
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'file_url'    => $this->file_url ?: null,
            'type'        => $this->type,
            'xp_reward'   => $this->xp_reward,
        ];

        if ($this->editId !== null) {
            Material::findOrFail($this->editId)->update($data);
            $message = 'Material updated successfully.';
        } else {
            Material::create($data);
            $message = 'Material created successfully.';
        }

        $this->closeModal();
        $this->resetPage();
        $this->dispatch('notify', message: $message);
    }

    /**
     * Permanently delete a material.
     * The wire:confirm directive in the view handles the browser confirmation.
     */
    public function delete(Material $material): void
    {
        $material->delete();
        $this->resetPage();
        $this->dispatch('notify', message: "'{$material->title}' has been deleted.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editId      = null;
        $this->title       = '';
        $this->description = '';
        $this->file_url    = '';
        $this->type        = 'document';
        $this->xp_reward   = 10;
        $this->resetValidation();
    }
}
