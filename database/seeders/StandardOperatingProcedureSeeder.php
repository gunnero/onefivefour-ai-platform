<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Seeder;

class StandardOperatingProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $department = Department::query()->where('organization_id', $organization->id)->where('name', 'Editorial')->firstOrFail();
        $site = Site::query()->where('organization_id', $organization->id)->where('slug', 'razbudise-mk')->firstOrFail();

        StandardOperatingProcedure::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'sop_key' => 'editorial-review',
                'version' => 1,
            ],
            [
                'department_id' => $department->id,
                'site_id' => $site->id,
                'title' => 'Editorial Review SOP',
                'status' => 'active',
                'purpose' => 'Prepare editorial work for human approval.',
                'trigger_description' => 'An Assignment reaches editorial review.',
                'inputs_schema' => ['required' => ['briefing', 'draft']],
                'steps' => [
                    ['name' => 'Review the briefing'],
                    ['name' => 'Check the output against policy'],
                    ['name' => 'Request human review'],
                ],
                'success_criteria' => ['Output is ready for human review.'],
                'quality_checks' => ['No direct publishing action is taken.'],
                'escalation_rules' => ['Escalate sensitive or low-confidence work.'],
                'output_expectations' => ['Review note and decision recommendation.'],
                'effective_from' => now(),
                'effective_to' => null,
                'metadata' => [],
            ],
        );
    }
}
