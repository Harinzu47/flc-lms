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
class GamifiedDashboard extends Component
{
    public string $activeTab = 'overview';

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();
        // TAHAP 1: GARIS PERTAHANAN KONTROLER (Role-Based Forking)
        if ($user->role === 'admin') {
            $totalStudents = User::where('role', 'member')->count();
            $pendingGradingCount = \App\Models\Submission::whereNull('score')
                ->where('is_flagged', false)
                ->count();
            $flaggedCount = \App\Models\Submission::where('is_flagged', true)->count();
            
            $topStudents = $this->activeTab === 'leaderboard'
                ? \App\Models\User::getCachedLeaderboard(10)
                : collect();

            $pendingSubmissions = \App\Models\Submission::whereNull('score')->get();

            return view('livewire.admin.dashboard-analytics', compact(
                'totalStudents',
                'pendingGradingCount',
                'flaggedCount',
                'topStudents',
                'pendingSubmissions'
            ))
            ->layout('layouts.base')
            ->title('Admin Analytics Command Center — FLC LMS');
        }
        // --- STUDENT (MEMBER) DASHBOARD ---
        // Load badges with pivot data (unlocked_at timestamp for sorting)
        $badges = $user->badges()
            ->orderByPivot('unlocked_at', 'desc')
            ->get();

        // Last 5 XP events — used for the "Recent Activity" feed
        $xpLogs = $user->xpLogs()
            ->latest()
            ->take(5)
            ->get();

        $leaderboard = \App\Models\User::getCachedLeaderboard(5);

        // Upcoming tasks with a future deadline (nearest first), max 3
        $upcomingTasks = \App\Models\Task::getUpcomingForUser($user);

        // Fetch all levels sorted by min_xp once to avoid N+1 query loops in the view
        $allLevels = \App\Models\Level::query()
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
        ->title('My Dashboard — FLC LMS');    }
}
