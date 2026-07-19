<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Models\Task;
use App\Models\Level;
use Illuminate\View\View;

trait RendersStudentDashboard
{
    /**
     * Get student dashboard data and render view.
     */
    protected function renderStudentDashboard(User $user): View
    {
        $badges = $user->badges()
            ->orderByPivot('unlocked_at', 'desc')
            ->get();

        $xpLogs = $user->xpLogs()
            ->latest()
            ->take(5)
            ->get();

        $leaderboard = User::getCachedLeaderboard(5);

        $upcomingTasks = Task::getUpcomingForUser($user);

        $allLevels = Level::query()
            ->orderBy('min_xp')
            ->get();

        return view('livewire.gamified-dashboard', compact(
            'user',
            'badges',
            'xpLogs',
            'leaderboard',
            'upcomingTasks',
            'allLevels',
        ))
        ->layout('layouts.gamified')
        ->title('My Dashboard — FLC LMS');
    }
}
