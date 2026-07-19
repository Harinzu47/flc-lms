<?php

declare(strict_types=1);

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
        Schema::table('badges', function (Blueprint $table) {
            $table->renameColumn('icon_url', 'icon');
            $table->renameColumn('criteria_value', 'target_value');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->string('icon')->default('🏅')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('icon')->default(null)->change();
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->renameColumn('icon', 'icon_url');
            $table->renameColumn('target_value', 'criteria_value');
        });
    }
};
