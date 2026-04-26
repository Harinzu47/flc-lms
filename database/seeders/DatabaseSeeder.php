<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — FLC UMJ Gamified LMS
 * ────────────────────────────────────────────────────────────────────────────
 * Populates the database with realistic, domain-specific data for a thesis
 * presentation demo (Software Engineering / Laravel / Cybersecurity context).
 *
 * Run order matters due to foreign key constraints:
 *   Users → Materials → Tasks → Submissions → XpLogs
 *
 * Usage:
 *   ./vendor/bin/sail artisan migrate:fresh --seed
 *   (or)
 *   php artisan migrate:fresh --seed
 * ────────────────────────────────────────────────────────────────────────────
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Step 1: Users ──────────────────────────────────────────────────────

        // Fixed admin account — used for grading portal login
        $admin = User::create([
            'name'              => 'Dosen Penguji',
            'email'             => 'admin@lms.local',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        // Fixed high-achieving student — Dashboard will be fully populated
        $star = User::create([
            'name'              => 'Mahasiswa Berprestasi',
            'email'             => 'student@lms.local',
            'password'          => Hash::make('password'),
            'role'              => 'member',
            'total_xp'          => 2500,
            'email_verified_at' => now(),
        ]);

        // 15 random students with varied XP so the Leaderboard is lively
        $randomStudents = User::factory()
            ->count(15)
            ->create(['role' => 'member', 'email_verified_at' => now()])
            ->each(function (User $user): void {
                $user->update(['total_xp' => fake()->numberBetween(100, 2000)]);
            });

        // Pool of non-admin users for submissions
        $allStudents = $randomStudents->prepend($star);

        // ── Step 2: Materials ──────────────────────────────────────────────────

        $materials = collect([
            [
                'title'       => 'Arsitektur Laravel 12 & Livewire 3',
                'description' => 'Penjelasan mendalam tentang arsitektur full-stack Laravel 12 dengan Livewire 3, mencakup server-side rendering, Alpine.js, dan pola komponen yang efisien.',
                'type'        => 'video',
                'xp_reward'   => 50,
                'file_url'    => 'https://www.youtube.com/watch?v=laravel12-livewire3',
            ],
            [
                'title'       => 'Pengantar Vulnerability Assessment (VAPT)',
                'description' => 'Modul pengenalan VAPT: metodologi penilaian kerentanan, alat utama (Nmap, Nikto, Burp Suite), dan cara mendokumentasikan temuan secara profesional.',
                'type'        => 'document',
                'xp_reward'   => 30,
                'file_url'    => null,
            ],
            [
                'title'       => 'Panduan Setup Docker & WSL2 untuk Dev',
                'description' => 'Langkah-langkah konfigurasi lingkungan pengembangan modern menggunakan Docker Desktop dengan WSL2 di Windows, termasuk Laravel Sail dan DevContainers.',
                'type'        => 'document',
                'xp_reward'   => 40,
                'file_url'    => null,
            ],
            [
                'title'       => 'Basic Penetration Testing Methodology',
                'description' => 'Video komprehensif tentang metodologi penetration testing: reconnaissance, scanning, exploitation, dan post-exploitation, sesuai standar OWASP.',
                'type'        => 'video',
                'xp_reward'   => 50,
                'file_url'    => 'https://www.youtube.com/watch?v=pentest-basics',
            ],
        ])->map(fn (array $data) => Material::create($data));

        // Grab individual materials for XpLog reference_id
        $vapt = $materials->firstWhere('title', 'Pengantar Vulnerability Assessment (VAPT)');

        // ── Step 3: Tasks ──────────────────────────────────────────────────────

        $taskCrud = Task::create([
            'title'       => 'Implementasi CRUD dengan Livewire',
            'description' => 'Buat sebuah aplikasi CRUD sederhana menggunakan Laravel Livewire 3. Implementasikan fitur create, read, update, dan delete untuk entitas "Produk" dengan validasi lengkap dan notifikasi toast.',
            'type'        => 'file_upload',
            'base_xp'     => 100,
            'deadline'    => now()->addDays(7),
        ]);

        $taskVapt = Task::create([
            'title'       => 'Laporan Scanning Vulnerability Web',
            'description' => 'Lakukan vulnerability scanning pada target lab yang disediakan menggunakan Nmap dan Nikto. Dokumentasikan semua temuan dalam format laporan profesional (PDF) sesuai template yang diberikan.',
            'type'        => 'file_upload',
            'base_xp'     => 150,
            'deadline'    => now()->addDays(3),
        ]);

        Task::create([
            'title'       => 'Esai Pemahaman CI/CD Pipeline',
            'description' => 'Tulis esai 1000–1500 kata yang menjelaskan konsep CI/CD Pipeline, perbedaan Continuous Integration dan Continuous Delivery, serta contoh implementasinya menggunakan GitHub Actions atau GitLab CI.',
            'type'        => 'essay',
            'base_xp'     => 80,
            'deadline'    => null,
        ]);

        // ── Step 4: Submissions ────────────────────────────────────────────────

        // 2 pending submissions for taskCrud from random students
        $randomStudents->take(2)->each(function (User $user) use ($taskCrud): void {
            Submission::create([
                'task_id'     => $taskCrud->id,
                'user_id'     => $user->id,
                'answer_text' => null,
                'file_url'    => 'submissions/livewire-crud-' . $user->id . '.zip',
                'score'       => null,
                'status'      => 'pending',
            ]);
        });

        // 1 graded submission for the star student on taskVapt
        Submission::create([
            'task_id'     => $taskVapt->id,
            'user_id'     => $star->id,
            'answer_text' => null,
            'file_url'    => 'submissions/vapt-laporan-mahasiswa-berprestasi.pdf',
            'score'       => 90,
            'status'      => 'graded',
        ]);

        // ── Step 5: XP Logs (Recent Activity for star student) ────────────────

        // Log 1: Material read — 2 days ago
        $log1 = XpLog::create([
            'user_id'      => $star->id,
            'action'       => 'Membaca materi: Pengantar Vulnerability Assessment (VAPT)',
            'xp_earned'    => 30,
            'reference_id' => $vapt->id,
        ]);
        $log1->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->save();

        // Log 2: Task graded — 1 day ago (score 90% of 150 = 135 XP)
        $log2 = XpLog::create([
            'user_id'      => $star->id,
            'action'       => 'Tugas Dinilai: Laporan Scanning Vulnerability Web',
            'xp_earned'    => 135,
            'reference_id' => $taskVapt->id,
        ]);
        $log2->forceFill(['created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)])->save();

        // ── Summary ────────────────────────────────────────────────────────────

        $this->command->info('');
        $this->command->info('✅  FLC UMJ LMS seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Name', 'Email', 'Password'],
            [
                ['Admin',   $admin->name, $admin->email, 'password'],
                ['Student', $star->name,  $star->email,  'password'],
                ['(+15 random students with varied XP)', '', '', ''],
            ]
        );
        $this->command->info('   Materials : ' . Material::count());
        $this->command->info('   Tasks     : ' . Task::count());
        $this->command->info('   Submissions: ' . Submission::count() . ' (2 pending, 1 graded)');
        $this->command->info('   XP Logs   : ' . XpLog::count());
        $this->command->info('');
    }
}
