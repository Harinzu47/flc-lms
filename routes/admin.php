<?php

declare(strict_types=1);

use App\Livewire\Admin\BadgeManager;
use App\Livewire\Admin\CourseManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\GradingStation;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Decoupled admin routes for the FLC LMS portal. All routes are protected
| under session authentication and strict role-based enforcement middleware.
|
| Instruktur: Course Manager + Grading Station
| Admin:      User Manager + Badge Manager
|
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // ── Instruktur Routes (Course & Grading) ─────────────────────────────
    Route::middleware('role:instruktur')->group(function () {
        Route::get('/courses', CourseManager::class)->name('courses');
        Route::get('/grading', GradingStation::class)->name('grading');
    });

    // ── Admin Routes (User & Badge Management) ───────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', UserManager::class)->name('users');
        Route::get('/badges', BadgeManager::class)->name('badges');
    });
});
