<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $site = Site::query()->where('organization_id', $organization->id)->where('slug', 'razbudise-mk')->firstOrFail();
        $department = Department::query()->where('organization_id', $organization->id)->where('name', 'Editorial')->firstOrFail();
        $employee = Employee::query()->where('organization_id', $organization->id)->where('full_name', 'Elena Markova')->firstOrFail();
        $sop = StandardOperatingProcedure::query()->where('organization_id', $organization->id)->where('sop_key', 'editorial-review')->firstOrFail();

        Assignment::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'title' => 'Review first Razbudise editorial package',
            ],
            [
                'site_id' => $site->id,
                'department_id' => $department->id,
                'employee_id' => $employee->id,
                'standard_operating_procedure_id' => $sop->id,
                'assignment_type' => 'editorial_review',
                'priority' => 'normal',
                'status' => 'pending',
                'briefing' => ['summary' => 'Confirm the first editorial package is ready for human review.'],
                'expected_output' => 'A review note and recommendation for the human supervisor.',
                'input_payload' => ['source' => 'seed'],
                'output_payload' => null,
                'required_capability_keys' => ['editing'],
                'confidence_score' => null,
                'quality_score' => null,
                'escalation_required' => false,
                'review_required' => true,
                'review_path' => 'Human supervisor approval',
                'due_at' => now()->addDay(),
            ],
        );
    }
}
