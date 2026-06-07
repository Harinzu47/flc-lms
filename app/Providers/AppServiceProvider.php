<?php

namespace App\Providers;

use App\Events\XpEarned;
use App\Listeners\SyncUserLevel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            XpEarned::class,
            SyncUserLevel::class
        );

        Event::listen(
            XpEarned::class,
            \App\Listeners\EvaluateBadgeUnlocks::class
        );
    }
}
