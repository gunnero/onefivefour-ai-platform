<?php

namespace Database\Factories;

use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Organization;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessProcessStep>
 */
class BusinessProcessStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'organization_id' => Organization::factory(),
            'business_process_definition_id' => BusinessProcessDefinition::factory(),
            'department_id' => Department::factory(),
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'required_capability_id' => Capability::factory(),
            'step_key' => Str::slug($name),
            'name' => Str::title($name),
            'status' => 'active',
            'sort_order' => $this->faker->numberBetween(1, 20),
            'description' => $this->faker->sentence(),
            'expected_output' => $this->faker->sentence(),
            'dependency_rules' => [],
            'approval_required' => false,
            'approval_rule' => null,
            'retry_rule' => ['max_attempts' => 1],
            'failure_rule' => ['on_failure' => 'block_run'],
            'escalation_rule' => null,
            'metadata' => [],
        ];
    }
}
