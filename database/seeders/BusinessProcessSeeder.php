<?php

namespace Database\Seeders;

use App\Models\AssignmentTemplate;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\DepartmentQueue;
use App\Models\Organization;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessProcessSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $editorialDepartment = Department::query()
            ->where('organization_id', $organization->id)
            ->where('name', 'Editorial')
            ->firstOrFail();
        $editorialReviewSop = StandardOperatingProcedure::query()
            ->where('organization_id', $organization->id)
            ->where('sop_key', 'editorial-review')
            ->where('version', 1)
            ->first();

        $definition = BusinessProcessDefinition::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'process_key' => 'prepare-editorial-package',
                'version' => 1,
            ],
            [
                'owning_department_id' => $editorialDepartment->id,
                'manager_employee_id' => null,
                'name' => 'Prepare Editorial Package',
                'status' => 'active',
                'purpose' => 'Coordinate repeatable editorial package preparation without coupling the engine to publishing.',
                'trigger_description' => 'A reusable editorial package request is ready to move through Organizational Core capabilities.',
                'input_schema' => [
                    'type' => 'object',
                    'required' => ['brief'],
                    'properties' => [
                        'brief' => ['type' => 'object'],
                        'site_context' => ['type' => 'object'],
                    ],
                ],
                'completion_criteria' => [
                    'all_required_steps_completed' => true,
                    'delivery_ready_step_completed' => true,
                    'no_article_publishing' => true,
                ],
                'default_site_required' => false,
                'metadata' => ['seeded_by' => self::class],
                'activated_at' => now(),
                'retired_at' => null,
            ],
        );

        $steps = [
            [
                'name' => 'Research',
                'department' => 'Research',
                'capability' => 'Research',
                'assignment_title' => 'Prepare research package',
                'expected_output' => 'A source-backed research package with key facts, context, and open questions.',
                'dependency' => null,
                'approval_required' => false,
                'sop' => null,
            ],
            [
                'name' => 'Writing',
                'department' => 'Writing',
                'capability' => 'Writing',
                'assignment_title' => 'Draft editorial package',
                'expected_output' => 'A structured draft prepared from the approved research package.',
                'dependency' => 'Research',
                'approval_required' => false,
                'sop' => null,
            ],
            [
                'name' => 'Localization',
                'department' => 'Localization',
                'capability' => 'Localization',
                'assignment_title' => 'Localize editorial package',
                'expected_output' => 'Localized wording, examples, and context suitable for the target audience.',
                'dependency' => 'Writing',
                'approval_required' => false,
                'sop' => null,
            ],
            [
                'name' => 'Fact Check',
                'department' => 'Trust & Safety',
                'capability' => 'Fact Checking',
                'assignment_title' => 'Verify editorial package claims',
                'expected_output' => 'A fact-check note confirming claims, sources, and unresolved risks.',
                'dependency' => 'Localization',
                'approval_required' => true,
                'sop' => null,
            ],
            [
                'name' => 'SEO',
                'department' => 'SEO',
                'capability' => 'SEO',
                'assignment_title' => 'Prepare SEO package',
                'expected_output' => 'SEO title, description, keyword notes, and discovery recommendations.',
                'dependency' => 'Fact Check',
                'approval_required' => false,
                'sop' => null,
            ],
            [
                'name' => 'Editor Review',
                'department' => 'Editorial',
                'capability' => 'Editing',
                'assignment_title' => 'Review editorial package',
                'expected_output' => 'An editorial review note with required revisions or approval recommendation.',
                'dependency' => 'SEO',
                'approval_required' => true,
                'sop' => $editorialReviewSop,
            ],
            [
                'name' => 'Human Approval',
                'department' => 'Editorial',
                'capability' => 'Editing',
                'assignment_title' => 'Request human approval',
                'expected_output' => 'A recorded human approval decision before the package can be marked ready.',
                'dependency' => 'Editor Review',
                'approval_required' => true,
                'sop' => $editorialReviewSop,
            ],
            [
                'name' => 'Delivery Ready',
                'department' => 'Operations',
                'capability' => 'Editing',
                'assignment_title' => 'Mark editorial package delivery ready',
                'expected_output' => 'A delivery-ready status note with handoff details and no publishing action.',
                'dependency' => 'Human Approval',
                'approval_required' => false,
                'sop' => null,
            ],
        ];

        foreach ($steps as $index => $stepData) {
            $department = Department::query()
                ->where('organization_id', $organization->id)
                ->where('name', $stepData['department'])
                ->firstOrFail();
            $capability = Capability::query()
                ->where('name', $stepData['capability'])
                ->firstOrFail();
            $stepKey = Str::slug($stepData['name']);
            $dependencyRules = $stepData['dependency'] === null
                ? []
                : [[
                    'step_key' => Str::slug($stepData['dependency']),
                    'required_status' => 'completed',
                ]];

            $step = BusinessProcessStep::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'business_process_definition_id' => $definition->id,
                    'step_key' => $stepKey,
                ],
                [
                    'department_id' => $department->id,
                    'standard_operating_procedure_id' => $stepData['sop']?->id,
                    'required_capability_id' => $capability->id,
                    'name' => $stepData['name'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'description' => "{$stepData['name']} step for the Prepare Editorial Package process.",
                    'expected_output' => $stepData['expected_output'],
                    'dependency_rules' => $dependencyRules,
                    'approval_required' => $stepData['approval_required'],
                    'approval_rule' => $stepData['approval_required']
                        ? ['type' => 'human_approval_gate']
                        : null,
                    'retry_rule' => ['max_attempts' => 1],
                    'failure_rule' => ['on_failure' => 'block_process_run'],
                    'escalation_rule' => $stepData['approval_required']
                        ? ['escalate_to' => 'department_manager']
                        : null,
                    'metadata' => ['seeded_by' => self::class],
                ],
            );

            AssignmentTemplate::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'business_process_step_id' => $step->id,
                    'template_key' => $stepKey,
                ],
                [
                    'business_process_definition_id' => $definition->id,
                    'department_id' => $department->id,
                    'standard_operating_procedure_id' => $stepData['sop']?->id,
                    'required_capability_id' => $capability->id,
                    'title_template' => $stepData['assignment_title'],
                    'assignment_type' => 'business_process_step',
                    'priority' => 'normal',
                    'briefing_template' => [
                        'process' => 'Prepare Editorial Package',
                        'step' => $stepData['name'],
                        'expected_input' => $dependencyRules === [] ? 'Process Run input payload' : 'Previous completed step output',
                    ],
                    'expected_output' => $stepData['expected_output'],
                    'input_mapping' => [
                        'business_process_run_step_id' => 'source',
                        'dependency_rules' => $dependencyRules,
                    ],
                    'review_required' => $stepData['approval_required'],
                    'review_path' => $stepData['approval_required'] ? 'Human approval gate' : null,
                    'due_offset_minutes' => 1440,
                    'metadata' => ['seeded_by' => self::class],
                ],
            );
        }

        foreach (collect($steps)->pluck('department')->unique() as $departmentName) {
            $department = Department::query()
                ->where('organization_id', $organization->id)
                ->where('name', $departmentName)
                ->firstOrFail();

            DepartmentQueue::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'queue_key' => Str::slug($departmentName).'-queue',
                ],
                [
                    'department_id' => $department->id,
                    'site_id' => null,
                    'name' => "{$departmentName} Queue",
                    'status' => 'active',
                    'default_routing_strategy' => 'first_available',
                    'max_active_assignments_per_employee' => 3,
                    'pending_work_request_count' => 0,
                    'blocked_work_request_count' => 0,
                    'failed_work_request_count' => 0,
                    'last_selected_employee_id' => null,
                    'routing_paused_reason' => null,
                    'routing_paused_until' => null,
                    'metadata' => ['seeded_by' => self::class],
                ],
            );
        }
    }
}
