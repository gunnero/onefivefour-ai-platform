<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
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
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(2),
            'status' => 'active',
            'site_type' => 'publication',
            'primary_domain' => $this->faker->domainName(),
            'default_locale' => 'mk',
            'timezone' => 'Europe/Skopje',
            'audience_notes' => $this->faker->sentence(),
            'editorial_context' => $this->faker->paragraph(),
            'metadata' => [],
        ];
    }
}
