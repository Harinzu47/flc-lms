<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Level>
 */
final class LevelFactory extends Factory
{
    protected $model = Level::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'min_xp' => $this->faker->unique()->numberBetween(0, 10000),
            'icon_url' => $this->faker->imageUrl(),
        ];
    }
}
