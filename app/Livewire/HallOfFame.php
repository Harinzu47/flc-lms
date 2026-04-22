<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Full-page Livewire component for the Hall of Fame (Leaderboard).
 *
 * Stitch AI Screen ID: 3eaf16a6930a4150a3d7990abc1a5a55
 *
 * Architecture:
 *  - All data is computed in render() and passed directly to the view.
 *    No public properties — keeps the component stateless and always fresh.
 *  - $currentUserRank uses a COUNT query (not collection search) so it stays
 *    accurate even when the auth user is outside the top-50 collection.
 *  - currentLevel() / nextLevel() are called per-user in the view; for 50 rows
 *    this is acceptable. If performance becomes an issue, eager-load levels
 *    via a withCount or a single Level::orderBy('min_xp')->get() passed down.
 */
#[Layout('layouts.gamified')]
#[Title('Hall of Fame — FLC LMS')]
class HallOfFame extends Component
{
    public function render(): View
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Top 50 members by total XP, highest first.
        $topUsers = User::query()
            ->where('role', 'member')
            ->orderByDesc('total_xp')
            ->take(50)
            ->get();

        // Rank = number of members with MORE XP than the current user + 1.
        // Using a scalar COUNT avoids loading the entire table.
        $currentUserRank = User::query()
            ->where('role', 'member')
            ->where('total_xp', '>', $authUser->total_xp)
            ->count() + 1;

        return view('livewire.hall-of-fame', compact('topUsers', 'currentUserRank'));
    }
}
