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
        Schema::table('users', function (Blueprint $table) {
            $table->json('peminatan_bahasa')->nullable()->after('role');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('kategori_bahasa')->default('general')->after('difficulty_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('kategori_bahasa');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('peminatan_bahasa');
        });
    }
};
