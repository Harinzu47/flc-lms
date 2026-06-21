<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\CourseManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\GamifiedDashboard;
use App\Livewire\GradingStation;
use App\Livewire\HallOfFame;
use App\Livewire\Library;
use App\Livewire\MaterialShow;
use App\Livewire\TaskShow;
use App\Livewire\CourseShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ── Dashboard (replaces Breeze's default closure) ─────────────────────────
Route::get('/dashboard', GamifiedDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // ── Profile ──────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    // ── Secure Submission Downloads ────────────────────────────────────────
    Route::get('/submissions/{submission}/download', [\App\Http\Controllers\SubmissionDownloadController::class, 'download'])
        ->name('submissions.download');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
