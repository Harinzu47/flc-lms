<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Material;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
final class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'file_url' => $this->faker->url(),
            'type' => $this->faker->randomElement(['video', 'document', 'link', 'article']),
            'xp_reward' => 10,
        ];
    }
}
