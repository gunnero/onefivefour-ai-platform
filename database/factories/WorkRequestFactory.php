<?php

namespace Database\Factories;

use App\Models\AssignmentTemplate;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkRequest>
 */
class WorkRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'organization_id' => Organization::factory(),
            'site_id' => Site::factory(),
            'department_id' => Department::factory(),
            'business_process_definition_id' => BusinessProcessDefinition::factory(),
            'business_process_run_id' => BusinessProcessRun::factory(),
            'business_process_run_step_id' => BusinessProcessRunStep::factory(),
            'assignment_template_id' => AssignmentTemplate::factory(),
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'required_capability_id' => Capability::factory(),
            'requested_by_user_id' => null,
            'source_type' => 'business_process',
            'source_id' => null,
            'work_request_key' => Str::uuid()->toString(),
            'title' => $title,
            'assignment_type' => 'business_process_step',
            'priority' => 'normal',
            'status' => 'pending',
            'routing_strategy' => 'first_available',
            'briefing' => ['summary' => $this->faker->sentence()],
            'expected_output' => $this->faker->sentence(),
            'input_payload' => ['source' => 'factory'],
            'review_required' => false,
            'review_path' => null,
            'due_at' => now()->addDay(),
            'assignment_id' => null,
            'blocked_reason' => null,
            'failure_reason' => null,
            'escalation_reason' => null,
            'requested_at' => now(),
            'routing_started_at' => null,
            'routed_at' => null,
            'dispatched_at' => null,
            'blocked_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
            'metadata' => [],
        ];
    }
}
