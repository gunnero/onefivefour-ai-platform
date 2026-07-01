<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'policy_key' => $this->faker->unique()->slug(2),
            'title' => $this->faker->sentence(4),
            'category' => 'editorial',
            'status' => 'active',
            'body' => $this->faker->paragraph(),
            'version' => 1,
            'effective_from' => now(),
            'effective_to' => null,
            'metadata' => [],
        ];
    }
}
