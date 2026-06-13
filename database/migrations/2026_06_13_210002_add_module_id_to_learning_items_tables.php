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
        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('module_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            // Drop old gating columns as they are now course-level gates
            if (Schema::hasColumn('materials', 'min_level_required')) {
                $table->dropForeign(['min_level_required']);
                $table->dropColumn('min_level_required');
            }

            if (Schema::hasColumn('materials', 'difficulty_level')) {
                $table->dropColumn('difficulty_level');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('module_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('modules')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'module_id')) {
                $table->dropForeign(['module_id']);
                $table->dropColumn('module_id');
            }

            // Restore the old gating columns
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])
                  ->default('beginner')
                  ->after('type');

            $table->foreignId('min_level_required')
                  ->nullable()
                  ->after('difficulty_level')
                  ->constrained('levels')
                  ->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'module_id')) {
                $table->dropForeign(['module_id']);
                $table->dropColumn('module_id');
            }
        });
    }
};
