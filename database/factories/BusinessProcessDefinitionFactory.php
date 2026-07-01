<?php

namespace Database\Factories;

use App\Models\BusinessProcessDefinition;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessProcessDefinition>
 */
class BusinessProcessDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'organization_id' => Organization::factory(),
            'owning_department_id' => Department::factory(),
            'manager_employee_id' => Employee::factory(),
            'process_key' => Str::slug($name),
            'name' => Str::title($name),
            'status' => 'draft',
            'version' => 1,
            'purpose' => $this->faker->sentence(),
            'trigger_description' => $this->faker->sentence(),
            'input_schema' => ['type' => 'object'],
            'completion_criteria' => ['all_required_steps_completed' => true],
            'default_site_required' => false,
            'metadata' => [],
            'activated_at' => null,
            'retired_at' => null,
        ];
    }
}
