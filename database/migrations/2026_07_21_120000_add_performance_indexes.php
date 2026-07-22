<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds missing performance-critical indexes identified in the performance audit:
 *
 *   1. submissions(user_id, status, task_id) — composite index covering the most common
 *      filter pattern (WHERE user_id = ? AND status = 'graded') used across CourseShow,
 *      GradingStation, RendersAdminDashboard, and EvaluateBadgeUnlocks.
 *
 *   2. users(role, total_xp) — composite index for leaderboard queries that filter
 *      WHERE role = 'member' ORDER BY total_xp DESC, used in HallOfFame,
 *      NotifyRankProximity, and User::getCachedLeaderboard().
 *
 *   3. pending_celebrations(user_id) — index for the per-request celebration polling
 *      in AppServiceProvider's Livewire dehydrate hook and CelebrationHub::mount().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->index(
                ['user_id', 'status', 'task_id'],
                'submissions_user_status_task_index'
            );
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(
                ['role', 'total_xp'],
                'users_role_total_xp_index'
            );
        });

        Schema::table('pending_celebrations', function (Blueprint $table) {
            $table->index('user_id', 'pending_celebrations_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_user_status_task_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_total_xp_index');
        });

        Schema::table('pending_celebrations', function (Blueprint $table) {
            $table->dropIndex('pending_celebrations_user_id_index');
        });
    }
};
