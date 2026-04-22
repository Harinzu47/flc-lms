<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Full-page Livewire component for the Student Gamified Dashboard.
 *
 * Stitch AI Screen ID: gamified-dashboard
 *
 * Design decision: No public properties. All dashboard data is passed
 * directly from render() to keep the view always fresh and avoid stale
 * Livewire state during reactive re-renders triggered by child events.
 *
 * Uses layouts.base to bypass Breeze's navigation shell — the Stitch
 * design supplies its own top app bar and mobile bottom nav.
 */
#[Layout('layouts.gamified')]
#[Title('My Dashboard — FLC LMS')]
class GamifiedDashboard extends Component
{
    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        // Load badges with pivot data (unlocked_at timestamp for sorting)
        $badges = $user->badges()
            ->orderByPivot('unlocked_at', 'desc')
            ->get();

        // Last 5 XP events — used for the "Recent Activity" feed
        $xpLogs = $user->xpLogs()
            ->latest()
            ->take(5)
            ->get();

        // Top 5 students by XP for the Mini Leaderboard
        $leaderboard = User::query()
            ->where('role', 'member')
            ->orderByDesc('total_xp')
            ->take(5)
            ->get();

        // Upcoming tasks with a future deadline (nearest first), max 3
        $upcomingTasks = \App\Models\Task::query()
            ->where('deadline', '>', now())
            ->orderBy('deadline')
            ->take(3)
            ->get();

        return view('livewire.gamified-dashboard', compact(
            'user',
            'badges',
            'xpLogs',
            'leaderboard',
            'upcomingTasks',
        ));
    }
}
