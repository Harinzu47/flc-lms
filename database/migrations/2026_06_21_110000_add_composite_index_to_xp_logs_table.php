<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('xp_logs', function (Blueprint $table) {
            /*
             |--------------------------------------------------------------------------
             | Prevent duplicate XP awards
             |--------------------------------------------------------------------------
             |
             | One user can only receive XP once for the same action and
             | reference_id combination.
             |
             | SQLite, MySQL and MariaDB all allow multiple NULL values
             | inside UNIQUE indexes, so actions without reference_id
             | remain valid.
             |
             */

            $table->unique(
                ['user_id', 'action', 'reference_id'],
                'xp_logs_user_action_ref_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xp_logs', function (Blueprint $table) {

            $table->dropUnique(
                'xp_logs_user_action_ref_unique'
            );

        });
    }
};
