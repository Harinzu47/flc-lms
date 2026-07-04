<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
final class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Badge',
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->word(),
            'criteria_type' => $this->faker->randomElement(['total_xp', 'materials_read', 'tasks_completed']),
            'target_value' => $this->faker->numberBetween(1, 10),
        ];
    }
}
