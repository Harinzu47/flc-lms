<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add xp_reward column to materials so the admin can configure
     * how many XP points a student earns for consuming each material.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->unsignedSmallInteger('xp_reward')->default(10)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('xp_reward');
        });
    }
};
