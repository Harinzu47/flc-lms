<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Concerns\RendersAdminDashboard;
use App\Livewire\Concerns\RendersStudentDashboard;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Full-page Livewire component for the Student Gamified Dashboard.
 *
 * Stitch AI Screen ID: gamified-dashboard
 */
class GamifiedDashboard extends Component
{
    use RendersAdminDashboard;
    use RendersStudentDashboard;

    public string $activeTab = 'overview';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user !== null) {
            if ($user->isInstruktur()) {
                $this->redirectRoute('admin.courses');

                return;
            }

            if ($user->isAdmin()) {
                $this->redirectRoute('admin.users');

                return;
            }
        }
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->isBph()) {
            return $this->renderAdminDashboard($user);
        }

        return $this->renderStudentDashboard($user);
    }
}
