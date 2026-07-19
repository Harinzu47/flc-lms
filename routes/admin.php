<?php

declare(strict_types=1);

use App\Livewire\Admin\CourseManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\Admin\BadgeManager;
use App\Livewire\GradingStation;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Decoupled admin routes for the FLC LMS portal. All routes are protected
| under both session authentication and admin role enforcement middleware.
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/grading', GradingStation::class)->name('grading');
    Route::get('/courses', CourseManager::class)->name('courses');
    Route::get('/users',   UserManager::class)->name('users');
    Route::get('/badges',  BadgeManager::class)->name('badges');
});
