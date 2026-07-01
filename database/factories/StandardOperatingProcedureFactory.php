<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StandardOperatingProcedure>
 */
class StandardOperatingProcedureFactory extends Factory
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
            'department_id' => Department::factory(),
            'site_id' => Site::factory(),
            'sop_key' => $this->faker->unique()->slug(2),
            'title' => $this->faker->sentence(4),
            'status' => 'active',
            'purpose' => $this->faker->sentence(),
            'trigger_description' => $this->faker->sentence(),
            'inputs_schema' => ['type' => 'object'],
            'steps' => [['name' => 'Review briefing']],
            'success_criteria' => ['Output meets briefing.'],
            'quality_checks' => ['Human review required when uncertain.'],
            'escalation_rules' => ['Escalate policy-bound work.'],
            'output_expectations' => ['Structured output.'],
            'version' => 1,
            'effective_from' => now(),
            'effective_to' => null,
            'metadata' => [],
        ];
    }
}
