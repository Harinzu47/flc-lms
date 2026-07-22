<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `last_rank_gap_notified` to the users table for rank-proximity
 * anti-spam tracking.  Nullable so existing rows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_rank_gap_notified')) {
                $table->unsignedInteger('last_rank_gap_notified')
                      ->nullable()
                      ->after('total_xp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_rank_gap_notified')) {
                $table->dropColumn('last_rank_gap_notified');
            }
        });
    }
};
