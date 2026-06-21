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
            // Add composite index for performance optimization on progress checks
            $table->index(['user_id', 'action', 'reference_id'], 'xp_logs_user_action_ref_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xp_logs', function (Blueprint $table) {
            $table->dropIndex('xp_logs_user_action_ref_index');
        });
    }
};
