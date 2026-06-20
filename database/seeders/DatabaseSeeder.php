<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Course;
use App\Models\Level;
use App\Models\Module;
use App\Models\Material;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — FLC UMJ Gamified LMS (Hierarchical Structure)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Step 1: Master Levels (using updateOrCreate to prevent duplicates) ──
        $level1 = Level::updateOrCreate(
            ['min_xp' => 0],
            ['name' => 'Beginner Level', 'icon_url' => 'levels/beginner.png']
        );

        $level2 = Level::updateOrCreate(
            ['min_xp' => 500],
            ['name' => 'Intermediate Level', 'icon_url' => 'levels/intermediate.png']
        );

        $level3 = Level::updateOrCreate(
            ['min_xp' => 1500],
            ['name' => 'Advanced Level', 'icon_url' => 'levels/advanced.png']
        );

        $level4 = Level::updateOrCreate(
            ['min_xp' => 3000],
            ['name' => 'Polyglot Master', 'icon_url' => 'levels/polyglot.png']
        );

        // ── Step 2: Master Badges (using updateOrCreate) ──
        Badge::updateOrCreate(
            ['name' => 'First Reader'],
            [
                'description' => 'Membaca modul materi pertama Anda.',
                'icon_url' => 'badges/first_reader.png',
                'criteria_type' => 'material_read',
                'criteria_value' => 1,
            ]
        );

        Badge::updateOrCreate(
            ['name' => 'Task Master'],
            [
                'description' => 'Menyelesaikan tugas pertama Anda.',
                'icon_url' => 'badges/task_master.png',
                'criteria_type' => 'task_graded',
                'criteria_value' => 1,
            ]
        );

        // ── Step 3: Users ──────────────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@lms.local'],
            [
                'name' => 'Dosen Penguji',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'level_id' => $level1->id,
                'email_verified_at' => now(),
            ]
        );

        $star = User::updateOrCreate(
            ['email' => 'student@lms.local'],
            [
                'name' => 'Mahasiswa Berprestasi',
                'password' => Hash::make('password'),
                'role' => 'member',
                'total_xp' => 2500,
                'level_id' => $level3->id,
                'email_verified_at' => now(),
            ]
        );

        // Make sure a few random students exist
        $studentsPool = collect();
        for ($i = 1; $i <= 5; $i++) {
            $studentsPool->push(
                User::updateOrCreate(
                    ['email' => "student{$i}@lms.local"],
                    [
                        'name' => "Mahasiswa Kelas {$i}",
                        'password' => Hash::make('password'),
                        'role' => 'member',
                        'total_xp' => 100 * $i,
                        'level_id' => $i >= 5 ? $level2->id : $level1->id,
                        'email_verified_at' => now(),
                    ]
                )
            );
        }
        $allStudents = $studentsPool->prepend($star);

        // ── Step 4: Courses, Modules, Materials, and Tasks ─────────────────────
        
        // Course 1: Beginner
        $course1 = Course::updateOrCreate(
            ['title' => 'Basic English Grammar'],
            [
                'description' => 'Mempelajari dasar-dasar tata bahasa Inggris seperti pronouns, simple present tense, dan penyusunan kalimat sederhana.',
                'difficulty_level' => 'beginner',
                'min_level_required' => $level1->id,
                'prerequisite_course_id' => null,
                'is_published' => true,
            ]
        );

        // Course 1 -> Module 1
        $module1_1 = Module::updateOrCreate(
            ['course_id' => $course1->id, 'title' => 'Introduction to Pronouns'],
            [
                'description' => 'Membahas penggunaan Subject, Object, dan Possessive Pronouns.',
                'sort_order' => 1,
            ]
        );

        $material1_1_1 = Material::updateOrCreate(
            ['title' => 'Panduan Lengkap Personal Pronouns'],
            [
                'module_id' => $module1_1->id,
                'description' => 'Ringkasan visual penggunaan I, me, my, mine, myself dalam struktur kalimat.',
                'type' => 'document',
                'xp_reward' => 10,
                'file_url' => null,
            ]
        );

        $material1_1_2 = Material::updateOrCreate(
            ['title' => 'Sintaksis Dasar & Struktur Kalimat'],
            [
                'module_id' => $module1_1->id,
                'description' => "# Sintaksis Dasar Bahasa Inggris\n\nSintaksis adalah cabang ilmu linguistik yang mempelajari struktur dan pembentukan kalimat. Dalam bahasa Inggris, kalimat yang benar wajib mengikuti pola dasar.\n\n## Pola Dasar Kalimat (Sentence Patterns)\n\nSetiap kalimat lengkap minimal harus memiliki **Subject (S)** dan **Verb (V)**. Berikut pola-pola yang umum digunakan:\n\n1. **S + V (Subject + Verb)**\n   * *Example:* \"She speaks.\"\n2. **S + V + O (Subject + Verb + Object)**\n   * *Example:* \"They learn English.\"\n3. **S + V + C (Subject + Verb + Complement)**\n   * *Example:* \"He is a competent teacher.\"\n\n> **Catatan Penting:** Kata kerja (*Verb*) harus menyesuaikan dengan subjeknya (*Subject-Verb Agreement*). Jika subjek tunggal (singular), tambahkan akhiran `-s` atau `-es` pada kata kerja dasar dalam bentuk Simple Present.\n\n### Contoh Latihan Pendek:\n* *Salah:* \"He play football.\"\n* *Benar:* \"He plays football.\"",
                'type' => 'article',
                'xp_reward' => 15,
                'file_url' => 'https://lms-assets.local/handout-sintaksis.pdf',
            ]
        );

        $task1_1_2 = Task::updateOrCreate(
            ['title' => 'Tugas: Menulis Paragraf Perkenalan'],
            [
                'module_id' => $module1_1->id,
                'description' => 'Tulis perkenalan diri Anda sepanjang minimal 5 kalimat, menggunakan setidaknya 3 jenis pronouns berbeda.',
                'type' => 'essay',
                'base_xp' => 50,
                'days_limit' => 7,
            ]
        );

        // Course 1 -> Module 2
        $module1_2 = Module::updateOrCreate(
            ['course_id' => $course1->id, 'title' => 'Simple Present Tense'],
            [
                'description' => 'Mempelajari bentuk waktu sekarang untuk menyatakan kebiasaan dan fakta umum.',
                'sort_order' => 2,
            ]
        );

        $material1_2_1 = Material::updateOrCreate(
            ['title' => 'Video Pembelajaran: Simple Present'],
            [
                'module_id' => $module1_2->id,
                'description' => 'Video interaktif yang menjelaskan perbedaan kata kerja untuk subjek tunggal dan jamak.',
                'type' => 'video',
                'xp_reward' => 15,
                'file_url' => 'https://www.youtube.com/watch?v=simple-present',
            ]
        );

        $task1_2_2 = Task::updateOrCreate(
            ['title' => 'Tugas: Rutinitas Harian'],
            [
                'module_id' => $module1_2->id,
                'description' => 'Tulis esai pendek (100 kata) mengenai rutinitas harian Anda dari pagi hingga malam.',
                'type' => 'essay',
                'base_xp' => 80,
                'days_limit' => 10,
            ]
        );

        // Course 2: Intermediate (Requires Course 1 & Level 2)
        $course2 = Course::updateOrCreate(
            ['title' => 'Intermediate Conversation & Speaking'],
            [
                'description' => 'Melatih kemampuan berbicara dalam konteks formal dan kasual, termasuk memberikan arah jalan dan berbicara di telepon.',
                'difficulty_level' => 'intermediate',
                'min_level_required' => $level2->id,
                'prerequisite_course_id' => $course1->id,
                'is_published' => true,
            ]
        );

        $module2_1 = Module::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Asking and Giving Directions'],
            [
                'description' => 'Bagaimana menanyakan arah dan memandu orang lain dengan kosakata arah.',
                'sort_order' => 1,
            ]
        );

        $material2_1_1 = Material::updateOrCreate(
            ['title' => 'Dokumen Kosakata Arah dan Peta'],
            [
                'module_id' => $module2_1->id,
                'description' => 'Kosakata petunjuk jalan seperti turn left, go straight, crossroad, dll.',
                'type' => 'document',
                'xp_reward' => 20,
                'file_url' => null,
            ]
        );

        $task2_1_2 = Task::updateOrCreate(
            ['title' => 'Tugas Percakapan Petunjuk Arah'],
            [
                'module_id' => $module2_1->id,
                'description' => 'Unggah rekaman suara Anda memberikan arah dari stasiun terdekat menuju kampus.',
                'type' => 'file_upload',
                'base_xp' => 100,
                'days_limit' => 5,
            ]
        );

        // ── Step 5: Seeding Submissions & XP Logs (For Demo) ───────────────────
        
        // Let's seed a completed/graded task for Mahasiswa Berprestasi on task1_1_2
        $submission = Submission::updateOrCreate(
            [
                'task_id' => $task1_1_2->id,
                'user_id' => $star->id,
            ],
            [
                'answer_text' => 'Hello, I am a star student. I love language and communication. My favorite hobby is reading.',
                'file_url' => null,
                'score' => 90,
                'status' => 'graded',
            ]
        );

        // Log XP for reading material1_1_1 and grading of task1_1_2
        XpLog::updateOrCreate(
            [
                'user_id' => $star->id,
                'action' => 'material_read',
                'reference_id' => $material1_1_1->id,
            ],
            [
                'xp_earned' => 10,
            ]
        );

        XpLog::updateOrCreate(
            [
                'user_id' => $star->id,
                'action' => 'task_graded',
                'reference_id' => $task1_1_2->id,
            ],
            [
                'xp_earned' => 45, // 90% of 50 base XP = 45 XP
            ]
        );

        // Seed some pending submissions for admin grading station demo
        $otherStudent = $studentsPool->first();
        if ($otherStudent) {
            Submission::updateOrCreate(
                [
                    'task_id' => $task1_1_2->id,
                    'user_id' => $otherStudent->id,
                ],
                [
                    'answer_text' => 'My name is student classes. I want to learn English properly.',
                    'file_url' => null,
                    'score' => null,
                    'status' => 'pending',
                ]
            );
        }

        $this->command->info('✅ FLC UMJ LMS Hierarchical Seeding Complete!');
    }
}
