<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrate the users.role ENUM from the legacy 2-role model ('admin', 'member')
 * to the strict 4-role RBAC model ('peserta', 'instruktur', 'admin', 'bph').
 *
 * Data migration:
 *   - 'member' → 'peserta' (all existing students)
 *   - 'admin'  → 'admin'   (unchanged — existing admins retain their role)
 *
 * Strategy:
 *   - MySQL: ALTER TABLE … MODIFY COLUMN (native ENUM support with 3-step safe conversion)
 *   - SQLite: Column is stored as TEXT, so we only need the data migration.
 *             Laravel's SQLite schema treats ENUM as TEXT under the hood.
 */
return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Step 1: Perlebar ENUM sementara agar bisa menampung data lama ('member') dan data baru
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('member', 'admin', 'peserta', 'instruktur', 'bph') NOT NULL DEFAULT 'peserta'");
        }

        // Step 2: Migrate existing data — rename 'member' → 'peserta' (Berlaku untuk semua driver)
        DB::table('users')
            ->where('role', 'member')
            ->update(['role' => 'peserta']);

        if ($driver === 'mysql') {
            // Step 3: Kunci kembali ENUM secara ketat hanya untuk 4 aktor yang baru
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('peserta', 'instruktur', 'admin', 'bph') NOT NULL DEFAULT 'peserta'");
        }

        // Step 4: For SQLite, update the default via Schema (TEXT column)
        if ($driver === 'sqlite') {
            Schema::table('users', function ($table) {
                $table->string('role')->default('peserta')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Step 1: Perlebar ENUM sementara untuk menerima 'member' saat rollback
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('peserta', 'instruktur', 'admin', 'bph', 'member') NOT NULL DEFAULT 'member'");
        }

        // Step 2: Reverse data migration
        DB::table('users')
            ->where('role', 'peserta')
            ->update(['role' => 'member']);

        // Collapse instruktur and bph back to admin (lossy but safe rollback)
        DB::table('users')
            ->whereIn('role', ['instruktur', 'bph'])
            ->update(['role' => 'admin']);

        if ($driver === 'mysql') {
            // Step 3: Shrink the ENUM back to the original 2 roles
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'member') NOT NULL DEFAULT 'member'");
        }

        if ($driver === 'sqlite') {
            Schema::table('users', function ($table) {
                $table->string('role')->default('member')->change();
            });
        }
    }
};