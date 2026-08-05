<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft deletes to core academic models
        $tablesWithSoftDeletes = ['users', 'courses', 'modules', 'tasks', 'submissions'];
        
        foreach ($tablesWithSoftDeletes as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 2. Convert FK constraints from cascadeOnDelete to restrictOnDelete
        
        // Helper to safely drop foreign keys by column name
        $dropFkByColumn = function($tableName, $columnName) {
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->dropForeign([$columnName]);
                });
                return;
            }

            $fks = Schema::getForeignKeys($tableName);
            foreach ($fks as $fk) {
                if (in_array($columnName, $fk['columns'])) {
                    Schema::table($tableName, function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk['name']);
                    });
                }
            }
        };

        $dropFkByColumn('modules', 'course_id');
        Schema::table('modules', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->restrictOnDelete();
        });

        $dropFkByColumn('materials', 'module_id');
        Schema::table('materials', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->restrictOnDelete();
        });

        $dropFkByColumn('tasks', 'module_id');
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->restrictOnDelete();
        });

        $dropFkByColumn('submissions', 'task_id');
        $dropFkByColumn('submissions', 'user_id');
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('task_id')->references('id')->on('tasks')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        $dropFkByColumn('xp_logs', 'user_id');
        Schema::table('xp_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        $dropFkByColumn('user_task_starts', 'user_id');
        $dropFkByColumn('user_task_starts', 'task_id');
        Schema::table('user_task_starts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Helper to safely drop foreign keys by column name
        $dropFkByColumn = function($tableName, $columnName) {
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->dropForeign([$columnName]);
                });
                return;
            }

            $fks = Schema::getForeignKeys($tableName);
            foreach ($fks as $fk) {
                if (in_array($columnName, $fk['columns'])) {
                    Schema::table($tableName, function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk['name']);
                    });
                }
            }
        };

        // Reverse FKs back to cascadeOnDelete
        $dropFkByColumn('user_task_starts', 'task_id');
        $dropFkByColumn('user_task_starts', 'user_id');
        Schema::table('user_task_starts', function (Blueprint $table) {
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $dropFkByColumn('xp_logs', 'user_id');
        Schema::table('xp_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $dropFkByColumn('submissions', 'user_id');
        $dropFkByColumn('submissions', 'task_id');
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });

        $dropFkByColumn('tasks', 'module_id');
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });

        $dropFkByColumn('materials', 'module_id');
        Schema::table('materials', function (Blueprint $table) {
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });

        $dropFkByColumn('modules', 'course_id');
        Schema::table('modules', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        // Drop soft deletes
        $tablesWithSoftDeletes = ['users', 'courses', 'modules', 'tasks', 'submissions'];
        
        foreach ($tablesWithSoftDeletes as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
