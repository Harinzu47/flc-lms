<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\GamifiedDashboard;
use App\Livewire\GradingStation;
use App\Livewire\HallOfFame;
use App\Livewire\MaterialShow;
use App\Livewire\TaskShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Dashboard (replaces Breeze's default closure) ─────────────────────────
// GamifiedDashboard uses layouts.base — it brings its own nav & shell.
Route::get('/dashboard', GamifiedDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // ── Profile ──────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Materials ─────────────────────────────────────────────────────────
    Route::get('/materials/{material}', MaterialShow::class)->name('materials.show');

    // ── Tasks ─────────────────────────────────────────────────────────────
    // {task} is automatically resolved to App\Models\Task via route model binding.
    Route::get('/tasks/{task}', TaskShow::class)->name('tasks.show');

    // ── Leaderboard ───────────────────────────────────────────────────────
    Route::get('/leaderboard', HallOfFame::class)->name('leaderboard');

    // ── Admin ─────────────────────────────────────────────────────────────
    // TODO: Replace 'auth' with a dedicated 'role:admin' middleware later.
    Route::get('/admin/grading', GradingStation::class)->name('admin.grading');
});

require __DIR__.'/auth.php';
