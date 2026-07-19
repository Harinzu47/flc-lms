<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CacheArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear caches to start from a clean state
        cache()->forget('levels.all');
        cache()->forget('leaderboard.top.5');
        cache()->forget('leaderboard.top.10');
    }

    public function test_all_levels_returns_level_models_from_cold_and_warm_cache(): void
    {
        // 1. Cold start: Database has levels, cache is empty
        $level1 = Level::factory()->create(['min_xp' => 0, 'name' => 'Beginner']);
        $level2 = Level::factory()->create(['min_xp' => 100, 'name' => 'Intermediate']);

        $this->assertFalse(cache()->has('levels.all'));

        $levels = User::allLevels();

        $this->assertCount(2, $levels);
        $this->assertInstanceOf(Level::class, $levels->first());
        $this->assertEquals('Beginner', $levels->first()->name);

        // Assert it was cached
        $this->assertTrue(cache()->has('levels.all'));

        // 2. Warm start: fetch again from cache
        $levelsFromCache = User::allLevels();
        $this->assertCount(2, $levelsFromCache);
        $this->assertInstanceOf(Level::class, $levelsFromCache->first());
        $this->assertEquals('Beginner', $levelsFromCache->first()->name);
        $this->assertTrue($levelsFromCache->first()->exists);
    }

    public function test_all_levels_cache_invalidates_when_level_is_saved_or_deleted(): void
    {
        $level1 = Level::factory()->create(['min_xp' => 0, 'name' => 'Beginner']);
        
        // Warm up cache
        User::allLevels();
        $this->assertTrue(cache()->has('levels.all'));

        // Create new level, cache should be invalidated
        $level2 = Level::factory()->create(['min_xp' => 100, 'name' => 'Intermediate']);
        $this->assertFalse(cache()->has('levels.all'));

        // Warm up cache again
        User::allLevels();
        $this->assertTrue(cache()->has('levels.all'));

        // Delete a level, cache should be invalidated
        $level1->delete();
        $this->assertFalse(cache()->has('levels.all'));
    }

    public function test_leaderboard_returns_hydrated_users_and_relations_from_cold_and_warm_cache(): void
    {
        $level = Level::factory()->create(['min_xp' => 0, 'name' => 'Starter']);
        $user1 = User::factory()->create(['role' => 'member', 'total_xp' => 500, 'level_id' => $level->id]);
        $user2 = User::factory()->create(['role' => 'member', 'total_xp' => 100, 'level_id' => $level->id]);

        $this->assertFalse(cache()->has('leaderboard.top.5'));

        // 1. Cold start
        $leaderboard = User::getCachedLeaderboard(5);
        $this->assertCount(2, $leaderboard);
        $this->assertInstanceOf(User::class, $leaderboard->first());
        $this->assertEquals($user1->id, $leaderboard->first()->id);
        $this->assertTrue($leaderboard->first()->relationLoaded('level'));
        $this->assertEquals('Starter', $leaderboard->first()->level->name);

        $this->assertTrue(cache()->has('leaderboard.top.5'));

        // 2. Warm start
        $leaderboardWarm = User::getCachedLeaderboard(5);
        $this->assertCount(2, $leaderboardWarm);
        $this->assertInstanceOf(User::class, $leaderboardWarm->first());
        $this->assertEquals($user1->id, $leaderboardWarm->first()->id);
        $this->assertTrue($leaderboardWarm->first()->relationLoaded('level'));
        $this->assertEquals('Starter', $leaderboardWarm->first()->level->name);
        $this->assertTrue($leaderboardWarm->first()->exists);
    }

    public function test_leaderboard_cache_invalidates_when_user_xp_or_details_change(): void
    {
        $level = Level::factory()->create(['min_xp' => 0]);
        $user = User::factory()->create(['role' => 'member', 'total_xp' => 100, 'level_id' => $level->id]);

        // Warm up cache
        User::getCachedLeaderboard(5);
        User::getCachedLeaderboard(10);
        $this->assertTrue(cache()->has('leaderboard.top.5'));
        $this->assertTrue(cache()->has('leaderboard.top.10'));

        // Update User XP
        $user->total_xp = 200;
        $user->save();

        $this->assertFalse(cache()->has('leaderboard.top.5'));
        $this->assertFalse(cache()->has('leaderboard.top.10'));

        // Warm up cache again
        User::getCachedLeaderboard(5);
        $this->assertTrue(cache()->has('leaderboard.top.5'));

        // Delete user
        $user->delete();
        $this->assertFalse(cache()->has('leaderboard.top.5'));
    }
}
