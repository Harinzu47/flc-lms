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
            ->with(['userStarts' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }, 'submissions' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->filter(function (\App\Models\Task $task) use ($user) {
                // Check if an associated submission exists
                $submission = $task->submissions->where('user_id', $user->id)->first();

                // Exclude tasks where a submission exists, UNLESS that submission is flagged (revisi)
                if ($submission && !$submission->is_flagged) {
                    return false;
                }

                // Check if days_limit is set
                if ($task->days_limit === null) {
                    return false;
                }

                // Check if a start record exists
                $deadline = $task->getPersonalDeadlineFor($user);
                if (!$deadline) {
                    return false;
                }

                // Keep only if computed personal deadline is in the future (> now())
                return $deadline->isFuture();
            })
            ->map(function (\App\Models\Task $task) use ($user) {
                // Dynamically assign legacy 'deadline' property as a Carbon instance for view compatibility
                $task->deadline = $task->getPersonalDeadlineFor($user);
                return $task;
            })
            ->sortBy(function (\App\Models\Task $task) use ($user) {
                return $task->deadline;
            })
            ->take(3);
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
        ));
    }
}
