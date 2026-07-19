<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear cached levels to prevent pollution across test runs
        cache()->forget('levels.all');
    }

    public function test_determine_level_from_collection_returns_correct_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0, 'name' => 'Novice']);
        $level2 = Level::factory()->create(['min_xp' => 100, 'name' => 'Apprentice']);
        $level3 = Level::factory()->create(['min_xp' => 500, 'name' => 'Expert']);

        $levelsCollection = collect([$level1, $level2, $level3]);

        $user = User::factory()->make(['total_xp' => 150]);

        // Act
        $matched = $user->determineLevelFromCollection($levelsCollection);

        // Assert
        $this->assertNotNull($matched);
        $this->assertEquals($level2->id, $matched->id);
        $this->assertEquals('Apprentice', $matched->name);
    }

    public function test_current_level_returns_highest_qualifying_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 200]);
        
        $user = User::factory()->create(['total_xp' => 250]);

        // Act & Assert
        $current = $user->currentLevel();
        $this->assertNotNull($current);
        $this->assertEquals($level2->id, $current->id);
    }

    public function test_current_level_returns_level_on_exact_xp_match(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 200]);

        $user = User::factory()->create(['total_xp' => 200]);

        // Act & Assert
        $current = $user->currentLevel();
        $this->assertNotNull($current);
        $this->assertEquals($level2->id, $current->id);
    }

    public function test_current_level_returns_null_if_no_levels_exist(): void
    {
        // Arrange
        $user = User::factory()->create(['total_xp' => 500]);

        // Act & Assert
        $current = $user->currentLevel();
        $this->assertNull($current);
    }

    public function test_current_level_returns_null_if_xp_is_below_lowest_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 100]);
        $user = User::factory()->create(['total_xp' => 50]);

        // Act & Assert
        $current = $user->currentLevel();
        $this->assertNull($current);
    }

    public function test_next_level_returns_immediate_subsequent_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 100]);
        $level3 = Level::factory()->create(['min_xp' => 300]);

        $user = User::factory()->create(['total_xp' => 120]);

        // Act & Assert
        $next = $user->nextLevel();
        $this->assertNotNull($next);
        $this->assertEquals($level3->id, $next->id);
    }

    public function test_next_level_returns_null_when_at_maximum_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 100]);

        $user = User::factory()->create(['total_xp' => 150]);

        // Act & Assert
        $next = $user->nextLevel();
        $this->assertNull($next);
    }

    public function test_progress_percentage_returns_hundred_at_max_level(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $user = User::factory()->create(['total_xp' => 10]);

        // Act & Assert
        $this->assertEquals(100, $user->progressPercentage());
    }

    public function test_progress_percentage_returns_correct_fractional_progress(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 100]); // 0 to 100 range

        $user = User::factory()->create(['total_xp' => 45]);

        // Act & Assert
        // Progress between 0 and 100 for 45 XP should be 45%
        $this->assertEquals(45, $user->progressPercentage());
    }

    public function test_progress_percentage_returns_correct_fraction_between_higher_levels(): void
    {
        // Arrange
        $level1 = Level::factory()->create(['min_xp' => 0]);
        $level2 = Level::factory()->create(['min_xp' => 100]);
        $level3 = Level::factory()->create(['min_xp' => 300]); // Range: 100 to 300 (200 XP range)

        $user = User::factory()->create(['total_xp' => 150]); // 50 XP above level 2

        // Act & Assert
        // 50 XP earned out of 200 XP range = 25% progress
        $this->assertEquals(25, $user->progressPercentage());
    }
}
