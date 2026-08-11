<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class LanguageTrackOnboarding extends Component
{
    public array $selectedTracks = [];
    public bool $isOpen = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user && $user->isPeserta() && empty($user->peminatan_bahasa)) {
            $this->isOpen = true;
        }
    }

    public function save(): void
    {
        if (empty($this->selectedTracks)) {
            $this->addError('selectedTracks', 'Silakan pilih setidaknya satu peminatan bahasa.');
            return;
        }

        $user = auth()->user();
        $user->update([
            'peminatan_bahasa' => $this->selectedTracks,
        ]);

        $this->isOpen = false;
        
        // Force a hard reload so the UI and queries update correctly
        $this->js('window.location.reload();');
    }

    public function render()
    {
        return view('livewire.language-track-onboarding');
    }
}
