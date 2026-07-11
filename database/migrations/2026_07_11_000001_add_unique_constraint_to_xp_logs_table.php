<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrades the composite index on xp_logs from a non-unique index
 * to a UNIQUE constraint for defense-in-depth against double XP awards.
 *
 * MySQL/SQLite treat NULL as distinct in unique indexes, so rows with
 * reference_id = NULL will not conflict with each other — safe for any
 * action type that does not use reference_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xp_logs', function (Blueprint $table) {
            // Drop the existing non-unique composite index
            $table->dropIndex('xp_logs_user_action_ref_index');

            // Add a unique constraint on the same columns
            $table->unique(
                ['user_id', 'action', 'reference_id'],
                'xp_logs_user_action_ref_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('xp_logs', function (Blueprint $table) {
            $table->dropUnique('xp_logs_user_action_ref_unique');

            // Restore the original non-unique index
            $table->index(
                ['user_id', 'action', 'reference_id'],
                'xp_logs_user_action_ref_index'
            );
        });
    }
};
