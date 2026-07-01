<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\WorkRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutingDecision>
 */
class RoutingDecisionFactory extends Factory
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
            'work_request_id' => WorkRequest::factory(),
            'department_id' => Department::factory(),
            'site_id' => Site::factory(),
            'assignment_id' => null,
            'selected_employee_id' => Employee::factory(),
            'strategy' => 'first_available',
            'status' => 'selected',
            'candidate_count' => 1,
            'eligible_count' => 1,
            'candidate_snapshot' => [['employee' => 'factory']],
            'eligibility_results' => [['eligible' => true]],
            'decision_reason' => 'Factory selected an eligible Employee.',
            'failure_reason' => null,
            'manager_override' => false,
            'override_reason' => null,
            'decided_by_type' => null,
            'decided_by_id' => null,
            'decided_at' => now(),
        ];
    }
}
