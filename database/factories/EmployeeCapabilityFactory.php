<?php

namespace Database\Factories;

use App\Models\Capability;
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeCapability>
 */
class EmployeeCapabilityFactory extends Factory
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
            'employee_id' => Employee::factory(),
            'capability_id' => Capability::factory(),
            'status' => 'active',
            'level' => 'standard',
            'notes' => null,
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }
}
