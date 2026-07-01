<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(2),
            'legal_name' => $this->faker->company().' LLC',
            'status' => 'active',
            'timezone' => 'Europe/Skopje',
            'locale' => 'en',
            'primary_domain' => $this->faker->domainName(),
            'summary' => $this->faker->sentence(),
            'metadata' => [],
        ];
    }
}
