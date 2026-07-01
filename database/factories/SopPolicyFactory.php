<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Policy;
use App\Models\SopPolicy;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SopPolicy>
 */
class SopPolicyFactory extends Factory
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
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'policy_id' => Policy::factory(),
        ];
    }
}
