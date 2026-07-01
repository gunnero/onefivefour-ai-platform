<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AssignmentTemplate;
use App\Models\AuditLog;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\DepartmentQueue;
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Organization;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use App\Services\BusinessProcess\WorkRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkRouterTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_strategy_records_manual_required_decision_without_assignment(): void
    {
        $fixture = $this->workRequestFixture('manual');
        $this->employee($fixture, 'Anna Manual');
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('manual', $decision->strategy);
        $this->assertSame('manual_required', $decision->status);
        $this->assertNull($decision->selected_employee_id);
        $this->assertSame(1, $decision->candidate_count);
        $this->assertSame(1, $decision->eligible_count);
        $this->assertFalse($decision->manager_override);
        $this->assertSame('Manual routing requires a human or Manager to choose an Employee.', $decision->decision_reason);
        $this->assertSame('waiting_for_manual_selection', $fixture['workRequest']->refresh()->status);
        $this->assertSame($beforeAssignments, Assignment::query()->count());
    }

    public function test_first_available_selects_first_eligible_employee_in_stable_order_and_records_activity_and_audit(): void
    {
        $fixture = $this->workRequestFixture('first_available');
        $zara = $this->employee($fixture, 'Zara Research');
        $anna = $this->employee($fixture, 'Anna Research');
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('selected', $decision->status);
        $this->assertSame('first_available', $decision->strategy);
        $this->assertSame($anna->id, $decision->selected_employee_id);
        $this->assertSame(2, $decision->candidate_count);
        $this->assertSame(2, $decision->eligible_count);
        $this->assertSame('Selected first eligible Employee in stable order.', $decision->decision_reason);
        $this->assertSame('routed', $fixture['workRequest']->refresh()->status);
        $this->assertNotNull($fixture['workRequest']->routed_at);
        $this->assertSame($beforeAssignments, Assignment::query()->count());
        $this->assertTrue($decision->selectedEmployee->is($anna));
        $this->assertFalse($decision->selectedEmployee->is($zara));

        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $fixture['run']->id,
            'business_process_run_step_id' => $fixture['runStep']->id,
            'event_type' => 'routing_decision_created',
        ]);
        $this->assertDatabaseHas('process_logs', [
            'business_process_run_id' => $fixture['run']->id,
            'business_process_run_step_id' => $fixture['runStep']->id,
            'log_level' => 'info',
        ]);

        $activity = Activity::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('activity_type', 'routing_decision_selected')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($fixture['department']->id, $activity->department_id);
        $this->assertSame($decision->id, $activity->metadata['routing_decision_id']);

        $auditLog = AuditLog::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('auditable_type', RoutingDecision::class)
            ->where('auditable_id', $decision->id)
            ->where('event_type', 'routing_decision_selected')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('selected', $auditLog->after_state['status']);
        $this->assertSame($anna->id, $auditLog->after_state['selected_employee_id']);
    }

    public function test_least_busy_selects_eligible_employee_with_fewest_active_assignments(): void
    {
        $fixture = $this->workRequestFixture('least_busy');
        $busy = $this->employee($fixture, 'Busy Research');
        $quiet = $this->employee($fixture, 'Quiet Research');
        Assignment::factory()
            ->count(2)
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($busy)
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->create(['status' => 'pending']);
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('selected', $decision->status);
        $this->assertSame('least_busy', $decision->strategy);
        $this->assertSame($quiet->id, $decision->selected_employee_id);
        $this->assertSame('Selected eligible Employee with the lowest active Assignment count.', $decision->decision_reason);
        $this->assertSame($beforeAssignments, Assignment::query()->count());
    }

    public function test_round_robin_selects_next_employee_after_department_queue_pointer(): void
    {
        $fixture = $this->workRequestFixture('round_robin');
        $anna = $this->employee($fixture, 'Anna Research');
        $ben = $this->employee($fixture, 'Ben Research');
        $cara = $this->employee($fixture, 'Cara Research');
        $queue = DepartmentQueue::factory()
            ->for($fixture['organization'])
            ->for($fixture['department'])
            ->for($fixture['site'])
            ->for($anna, 'lastSelectedEmployee')
            ->create([
                'queue_key' => 'research-queue',
                'status' => 'active',
            ]);
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('selected', $decision->status);
        $this->assertSame('round_robin', $decision->strategy);
        $this->assertSame($ben->id, $decision->selected_employee_id);
        $this->assertSame($ben->id, $queue->refresh()->last_selected_employee_id);
        $this->assertSame('Selected next eligible Employee from Department Queue rotation.', $decision->decision_reason);
        $this->assertSame($beforeAssignments, Assignment::query()->count());
        $this->assertNotSame($cara->id, $decision->selected_employee_id);
    }

    public function test_capability_match_selects_employee_with_required_active_capability(): void
    {
        $fixture = $this->workRequestFixture('capability_match');
        $this->employee($fixture, 'No Capability', grantCapability: false);
        $match = $this->employee($fixture, 'Capability Match');
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('selected', $decision->status);
        $this->assertSame('capability_match', $decision->strategy);
        $this->assertSame($match->id, $decision->selected_employee_id);
        $this->assertSame(2, $decision->candidate_count);
        $this->assertSame(1, $decision->eligible_count);
        $this->assertSame('Selected eligible Employee with required active Capability.', $decision->decision_reason);
        $this->assertSame($beforeAssignments, Assignment::query()->count());
    }

    public function test_no_eligible_employee_blocks_work_request_and_records_failure_side_effects(): void
    {
        $fixture = $this->workRequestFixture('first_available');
        $this->employee($fixture, 'Paused Research', [
            'paused_at' => now(),
        ]);
        $this->employee($fixture, 'Inactive Research', [
            'employment_status' => 'paused',
        ]);
        $this->employee($fixture, 'Retired Research', [
            'retired_at' => now(),
        ]);
        $this->employee($fixture, 'Archived Research', [
            'archived_at' => now(),
        ]);
        $beforeAssignments = Assignment::query()->count();

        $decision = app(WorkRouter::class)->route($fixture['workRequest']);

        $this->assertSame('no_eligible_employee', $decision->status);
        $this->assertNull($decision->selected_employee_id);
        $this->assertSame(4, $decision->candidate_count);
        $this->assertSame(0, $decision->eligible_count);
        $this->assertSame('No eligible Employee matched the Work Request.', $decision->failure_reason);
        $this->assertSame('blocked', $fixture['workRequest']->refresh()->status);
        $this->assertSame('No eligible Employee matched the Work Request.', $fixture['workRequest']->blocked_reason);
        $this->assertNotNull($fixture['workRequest']->blocked_at);
        $this->assertSame($beforeAssignments, Assignment::query()->count());

        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $fixture['run']->id,
            'business_process_run_step_id' => $fixture['runStep']->id,
            'event_type' => 'routing_failed',
        ]);
        $this->assertGreaterThanOrEqual(
            1,
            ProcessLog::query()
                ->where('business_process_run_id', $fixture['run']->id)
                ->where('business_process_run_step_id', $fixture['runStep']->id)
                ->whereNotNull('process_event_id')
                ->count(),
        );

        $activity = Activity::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('activity_type', 'routing_failed')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($decision->id, $activity->metadata['routing_decision_id']);

        $auditLog = AuditLog::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('auditable_type', RoutingDecision::class)
            ->where('auditable_id', $decision->id)
            ->where('event_type', 'routing_failed')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('no_eligible_employee', $auditLog->after_state['status']);
    }

    /**
     * @return array<string, mixed>
     */
    private function workRequestFixture(string $strategy): array
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create(['name' => 'Research']);
        $capability = Capability::factory()->create(['name' => 'Research', 'capability_key' => 'research']);
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();
        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->create(['status' => 'active']);
        $step = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'status' => 'active',
                'step_key' => 'research',
            ]);
        $template = AssignmentTemplate::factory()
            ->for($organization)
            ->for($definition)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create();
        $run = BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create(['status' => 'running']);
        $runStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($run)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create(['status' => 'ready']);
        $workRequest = WorkRequest::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($definition)
            ->for($run)
            ->for($runStep, 'businessProcessRunStep')
            ->for($template, 'assignmentTemplate')
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'status' => 'pending',
                'routing_strategy' => $strategy,
                'assignment_id' => null,
            ]);

        return [
            'organization' => $organization,
            'site' => $site,
            'department' => $department,
            'capability' => $capability,
            'sop' => $sop,
            'definition' => $definition,
            'step' => $step,
            'template' => $template,
            'run' => $run,
            'runStep' => $runStep,
            'workRequest' => $workRequest,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function employee(array $fixture, string $name, array $attributes = [], bool $grantCapability = true): Employee
    {
        $employee = Employee::factory()
            ->for($fixture['organization'])
            ->for($fixture['department'])
            ->create(array_merge([
                'full_name' => $name,
                'employment_status' => 'active',
                'paused_at' => null,
                'retired_at' => null,
                'archived_at' => null,
            ], $attributes));

        if ($grantCapability) {
            EmployeeCapability::factory()
                ->for($fixture['organization'])
                ->for($employee)
                ->for($fixture['capability'])
                ->create(['status' => 'active']);
        }

        return $employee;
    }
}
