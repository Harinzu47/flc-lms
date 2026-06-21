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

        \Livewire\Livewire::listen('component.dehydrate', function ($component, $context) {
            if (auth()->check()) {
                $pending = \App\Models\PendingCelebration::where('user_id', auth()->id())->get();
                foreach ($pending as $item) {
                    $payload = $item->payload;
                    if ($item->type === 'badge-unlocked') {
                        $component->dispatch('badge-unlocked', name: $payload['name'] ?? '', description: $payload['description'] ?? '', icon: $payload['icon'] ?? '');
                    } elseif ($item->type === 'level-up') {
                        $component->dispatch('level-up', levelName: $payload['levelName'] ?? '', targetXp: $payload['targetXp'] ?? 0);
                    }
                    $item->delete();
                }
            }
        });
    }
}
