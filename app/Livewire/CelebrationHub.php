<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\PendingCelebration;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * CelebrationHub — Global gamification celebration overlay.
 *
 * Replaces the previous inline Blade `@php` DB queries with a proper
 * Livewire component.  On mount it fetches any pending celebrations
 * for the authenticated user, dispatches browser events so Alpine.js
 * can render the modals/confetti, and then deletes the consumed records.
 *
 * Usage in layouts:  <livewire:celebration-hub />
 */
final class CelebrationHub extends Component
{
    /** @var array<int, array{type: string, payload: array<string, mixed>}> */
    public array $celebrations = [];

    /**
     * Fetch and consume all pending celebrations for the authenticated user.
     */
    public function mount(): void
    {
        if (! auth()->check()) {
            return;
        }

        $pending = PendingCelebration::where('user_id', auth()->id())->get();

        foreach ($pending as $item) {
            $this->celebrations[] = [
                'type'    => $item->type,
                'payload' => $item->payload,
            ];

            $item->delete();
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.celebration-hub');
    }
}
