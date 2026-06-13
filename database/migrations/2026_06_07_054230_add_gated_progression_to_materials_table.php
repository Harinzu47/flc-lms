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
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])
                  ->default('beginner')
                  ->after('type');

            $table->foreignId('min_level_required')
                  ->nullable()
                  ->after('difficulty_level')
                  ->constrained('levels')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['min_level_required']);
            $table->dropColumn(['difficulty_level', 'min_level_required']);
        });
    }
};
