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

    // ── Admin Portal ───────────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/grading', GradingStation::class)->name('grading');
        Route::get('/courses', CourseManager::class)->name('courses');
        Route::get('/users',   UserManager::class)->name('users');
        Route::get('/badges',  \App\Livewire\Admin\BadgeManager::class)->name('badges');
    });
});

require __DIR__.'/auth.php';
