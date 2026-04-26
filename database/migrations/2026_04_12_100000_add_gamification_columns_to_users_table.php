<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds gamification columns to the users table:
 *   - level_id  : FK to levels (nullable — assigned after first XP action)
 *   - total_xp  : running XP total, indexed for leaderboard queries
 *
 * Kept as a separate migration because the base users migration was already
 * executed before these columns were finalised in the schema.
 *
 * NOTE: Both columns may already exist (added by 2026_04_12_011654).
 * hasColumn() guards make this migration idempotent.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'level_id')) {
                $table->foreignId('level_id')
                      ->nullable()
                      ->after('role')
                      ->constrained('levels')
                      ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'total_xp')) {
                $table->unsignedInteger('total_xp')
                      ->default(0)
                      ->after('level_id')
                      ->index(); // Indexed for leaderboard ORDER BY total_xp DESC
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'level_id')) {
                $table->dropForeign(['level_id']);
                $table->dropColumn('level_id');
            }

            if (Schema::hasColumn('users', 'total_xp')) {
                $table->dropColumn('total_xp');
            }
        });
    }
};
