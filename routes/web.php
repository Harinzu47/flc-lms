<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionDownloadController;
use App\Livewire\CourseShow;
use App\Livewire\GamifiedDashboard;
use App\Livewire\HallOfFame;
use App\Livewire\Library;
use App\Livewire\MaterialShow;
use App\Livewire\TaskShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ── Dashboard (all authenticated users — view differs by role) ────────────
Route::get('/dashboard', GamifiedDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ── Profile (accessible to all authenticated roles) ──────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Student Routes (peserta-only) ────────────────────────────────────────
Route::middleware(['auth', 'role:peserta'])->group(function () {
    // ── Courses (Student View) ───────────────────────────────────────────
    Route::get('/courses/{course}', CourseShow::class)->name('courses.show');

    // ── Materials ─────────────────────────────────────────────────────────
    Route::get('/materials/{material}', MaterialShow::class)->name('materials.show');

    // ── Library ───────────────────────────────────────────────────────────
    Route::get('/library', Library::class)->name('library');

    // ── Tasks ─────────────────────────────────────────────────────────────
    Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');

    // ── Leaderboard ───────────────────────────────────────────────────────
    Route::get('/leaderboard', HallOfFame::class)->name('leaderboard');
});

// ── Submission Downloads (peserta owns, instruktur grades) ────────────────
Route::middleware(['auth', 'role:peserta,instruktur'])->group(function () {
    Route::get('/submissions/{submission}/download', [SubmissionDownloadController::class, 'download'])
        ->name('submissions.download');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
