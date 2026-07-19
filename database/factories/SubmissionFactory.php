<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
final class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'answer_text' => $this->faker->paragraph(),
            'file_url' => null,
            'score' => null,
            'status' => 'pending',
            'is_flagged' => false,
            'review_comment' => null,
        ];
    }
}
