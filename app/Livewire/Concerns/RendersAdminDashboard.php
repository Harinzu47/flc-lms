<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Models\Submission;
use Illuminate\Support\Collection;
use Illuminate\View\View;

trait RendersAdminDashboard
{
    /**
     * Get admin analytics data and render view.
     */
    protected function renderAdminDashboard(User $user): View
    {
        $totalStudents = User::where('role', 'member')->count();
        $pendingGradingCount = Submission::whereNull('score')
            ->where('is_flagged', false)
            ->count();
        $flaggedCount = Submission::where('is_flagged', true)->count();
        
        $topStudents = $this->activeTab === 'leaderboard'
            ? User::getCachedLeaderboard(10)
            : collect();

        $pendingSubmissions = Submission::whereNull('score')
            ->with(['user', 'task'])
            ->get();

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
}
