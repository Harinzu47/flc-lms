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
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->index();
            }
            if (!Schema::hasColumn('submissions', 'review_comment')) {
                $table->text('review_comment')->nullable()->after('is_flagged');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('submissions', 'is_flagged')) {
                $table->dropIndex(['is_flagged']);
                $columns[] = 'is_flagged';
            }
            if (Schema::hasColumn('submissions', 'review_comment')) {
                $columns[] = 'review_comment';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
