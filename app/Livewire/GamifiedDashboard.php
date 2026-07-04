<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Livewire\Concerns\RendersAdminDashboard;
use App\Livewire\Concerns\RendersStudentDashboard;
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

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->renderAdminDashboard($user);
        }

        return $this->renderStudentDashboard($user);
    }
}
