<?php

namespace Database\Factories;

use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProcessRunStep>
 */
class BusinessProcessRunStepFactory extends Factory
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
            'business_process_run_id' => BusinessProcessRun::factory(),
            'business_process_step_id' => BusinessProcessStep::factory(),
            'assignment_id' => null,
            'department_id' => Department::factory(),
            'employee_id' => Employee::factory(),
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'required_capability_id' => Capability::factory(),
            'status' => 'pending',
            'sort_order' => $this->faker->numberBetween(1, 20),
            'attempt_number' => 1,
            'approval_required' => false,
            'approval_status' => null,
            'blocked_reason' => null,
            'failure_reason' => null,
            'input_payload' => ['source' => 'factory'],
            'output_payload' => null,
            'ready_at' => now(),
            'started_at' => null,
            'completed_at' => null,
            'blocked_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
