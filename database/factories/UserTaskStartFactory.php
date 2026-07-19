<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\UserTaskStart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserTaskStart>
 */
final class UserTaskStartFactory extends Factory
{
    protected $model = UserTaskStart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'started_at' => now(),
        ];
    }
}
