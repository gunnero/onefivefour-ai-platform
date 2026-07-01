<?php

namespace Database\Factories;

use App\Models\Capability;
use App\Models\Organization;
use App\Models\SopCapability;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SopCapability>
 */
class SopCapabilityFactory extends Factory
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
            'capability_id' => Capability::factory(),
            'required_level' => 'standard',
        ];
    }
}
