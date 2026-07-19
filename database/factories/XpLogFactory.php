<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\XpLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<XpLog>
 */
final class XpLogFactory extends Factory
{
    protected $model = XpLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['material_read', 'task_graded']),
            'xp_earned' => $this->faker->randomElement([10, 50, 100]),
            'reference_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
