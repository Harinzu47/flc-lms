<?php

namespace App\Providers;

use App\Events\XpEarned;
use App\Listeners\EvaluateBadgeUnlocks;
use App\Listeners\NotifyRankProximity;
use App\Listeners\SyncUserLevel;
use App\Models\Level;
use App\Models\PendingCelebration;
use App\Models\User;
use App\Observers\LevelObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        User::observe(UserObserver::class);
        Level::observe(LevelObserver::class);

        Event::listen(
            XpEarned::class,
            EvaluateBadgeUnlocks::class
        );

        Event::listen(
            XpEarned::class,
            NotifyRankProximity::class
        );

        Livewire::listen('component.dehydrate', function ($component, $context) {
            if (auth()->check()) {
                static $pending = null;
                static $dispatched = false;

                if ($pending === null) {
                    $pending = PendingCelebration::where('user_id', auth()->id())->get();
                }

                if (! $dispatched && $pending->isNotEmpty()) {
                    foreach ($pending as $item) {
                        $payload = $item->payload;
                        if ($item->type === 'badge-unlocked') {
                            $component->dispatch('badge-unlocked', name: $payload['name'] ?? '', description: $payload['description'] ?? '', icon: $payload['icon'] ?? '');
                        } elseif ($item->type === 'level-up') {
                            $component->dispatch('level-up', levelName: $payload['levelName'] ?? '', targetXp: $payload['targetXp'] ?? 0);
                        }
                        $item->delete();
                    }
                    $dispatched = true;
                }
            }
        });
    }
}
