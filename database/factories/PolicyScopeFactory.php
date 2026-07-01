<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Policy;
use App\Models\PolicyScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolicyScope>
 */
class PolicyScopeFactory extends Factory
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
            'policy_id' => Policy::factory(),
            'scope_type' => 'organization',
            'scope_id' => null,
        ];
    }
}
