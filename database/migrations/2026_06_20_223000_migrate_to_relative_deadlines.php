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
        // 1. Modify the tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('days_limit')->nullable()->after('base_xp');
            if (Schema::hasColumn('tasks', 'deadline')) {
                $table->dropColumn('deadline');
            }
        });

        // 2. Create the user_task_starts table
        Schema::create('user_task_starts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->timestamps();

            // Composite unique index to ensure user opens a task only once, boosting concurrency safety
            $table->unique(['user_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_task_starts');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('deadline')->nullable()->after('base_xp');
            if (Schema::hasColumn('tasks', 'days_limit')) {
                $table->dropColumn('days_limit');
            }
        });
    }
};
