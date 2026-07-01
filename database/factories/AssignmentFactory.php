<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
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
            'site_id' => Site::factory(),
            'department_id' => Department::factory(),
            'employee_id' => Employee::factory(),
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'business_process_run_id' => null,
            'business_process_run_step_id' => null,
            'work_request_id' => null,
            'routing_decision_id' => null,
            'title' => $this->faker->sentence(4),
            'assignment_type' => 'editorial_review',
            'priority' => 'normal',
            'status' => 'pending',
            'briefing' => ['summary' => $this->faker->sentence()],
            'expected_output' => $this->faker->sentence(),
            'input_payload' => ['source' => 'manual'],
            'output_payload' => null,
            'required_capability_keys' => ['editing'],
            'confidence_score' => null,
            'quality_score' => null,
            'escalation_required' => false,
            'review_required' => true,
            'review_path' => 'Human supervisor review',
            'due_at' => now()->addDay(),
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
