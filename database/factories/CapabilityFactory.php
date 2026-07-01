<?php

namespace Database\Factories;

use App\Models\Capability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Capability>
 */
class CapabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'capability_key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'category' => 'editorial',
            'status' => 'active',
            'metadata' => [],
        ];
    }
}
