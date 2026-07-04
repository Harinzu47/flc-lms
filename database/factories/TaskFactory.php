<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Module;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
final class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['essay', 'file_upload', 'quiz']),
            'base_xp' => 50,
            'days_limit' => $this->faker->numberBetween(1, 30),
        ];
    }
}
