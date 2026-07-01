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
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use App\Services\BusinessProcess\AssignmentDispatchService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_routing_decision_dispatches_assignment_and_records_side_effects(): void
    {
        $fixture = $this->dispatchFixture();

        $assignment = app(AssignmentDispatchService::class)->dispatch($fixture['decision']);

        $this->assertSame($fixture['organization']->id, $assignment->organization_id);
        $this->assertSame($fixture['site']->id, $assignment->site_id);
        $this->assertSame($fixture['department']->id, $assignment->department_id);
        $this->assertSame($fixture['employee']->id, $assignment->employee_id);
        $this->assertSame($fixture['sop']->id, $assignment->standard_operating_procedure_id);
        $this->assertSame($fixture['workRequest']->title, $assignment->title);
        $this->assertSame($fixture['workRequest']->assignment_type, $assignment->assignment_type);
        $this->assertSame($fixture['workRequest']->priority, $assignment->priority);
        $this->assertSame('pending', $assignment->status);
        $this->assertSame($fixture['workRequest']->briefing, $assignment->briefing);
        $this->assertSame($fixture['workRequest']->expected_output, $assignment->expected_output);
        $this->assertSame($fixture['workRequest']->input_payload, $assignment->input_payload);
        $this->assertSame(['research'], $assignment->required_capability_keys);
        $this->assertTrue($assignment->review_required);
        $this->assertSame($fixture['workRequest']->review_path, $assignment->review_path);
        $this->assertTrue($assignment->due_at->equalTo($fixture['workRequest']->due_at));
        $this->assertNull($assignment->started_at);
        $this->assertNull($assignment->completed_at);
        $this->assertSame($fixture['run']->id, $assignment->business_process_run_id);
        $this->assertSame($fixture['runStep']->id, $assignment->business_process_run_step_id);
        $this->assertSame($fixture['workRequest']->id, $assignment->work_request_id);
        $this->assertSame($fixture['decision']->id, $assignment->routing_decision_id);

        $this->assertSame('assignment_created', $fixture['workRequest']->refresh()->status);
        $this->assertSame($assignment->id, $fixture['workRequest']->assignment_id);
        $this->assertNotNull($fixture['workRequest']->dispatched_at);
        $this->assertSame($assignment->id, $fixture['decision']->refresh()->assignment_id);
        $this->assertSame('assignment_created', $fixture['runStep']->refresh()->status);
        $this->assertSame($assignment->id, $fixture['runStep']->assignment_id);
        $this->assertSame('running', $fixture['run']->refresh()->status);
        $this->assertSame($fixture['runStep']->id, $fixture['run']->current_run_step_id);

        $event = ProcessEvent::query()
            ->where('business_process_run_id', $fixture['run']->id)
            ->where('business_process_run_step_id', $fixture['runStep']->id)
            ->where('assignment_id', $assignment->id)
            ->where('event_type', 'assignment_created')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame($fixture['workRequest']->id, $event->payload['work_request_id']);
        $this->assertSame($fixture['decision']->id, $event->payload['routing_decision_id']);
        $this->assertSame($fixture['employee']->id, $event->payload['selected_employee_id']);

        $processLog = ProcessLog::query()
            ->where('business_process_run_id', $fixture['run']->id)
            ->where('business_process_run_step_id', $fixture['runStep']->id)
            ->where('assignment_id', $assignment->id)
            ->where('process_event_id', $event->id)
            ->first();

        $this->assertNotNull($processLog);
        $this->assertSame('info', $processLog->log_level);
        $this->assertSame($fixture['workRequest']->id, $processLog->context['work_request_id']);

        $activity = Activity::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('assignment_id', $assignment->id)
            ->where('activity_type', 'assignment_created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($fixture['employee']->id, $activity->employee_id);

        $auditLog = AuditLog::query()
            ->where('organization_id', $fixture['organization']->id)
            ->where('auditable_type', Assignment::class)
            ->where('auditable_id', $assignment->id)
            ->where('event_type', 'assignment_created')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('pending', $auditLog->after_state['status']);
        $this->assertSame($fixture['workRequest']->id, $auditLog->after_state['work_request_id']);
    }

    public function test_non_selected_routing_decision_is_rejected(): void
    {
        $fixture = $this->dispatchFixture(decisionAttributes: [
            'status' => 'manual_required',
            'selected_employee_id' => null,
        ]);

        $this->assertDispatchFails(
            $fixture['decision'],
            'Only selected Routing Decisions can dispatch Assignments.',
        );
        $this->assertSame(0, Assignment::query()->count());
    }

    public function test_missing_selected_employee_is_rejected(): void
    {
        $fixture = $this->dispatchFixture(decisionAttributes: [
            'selected_employee_id' => null,
        ]);

        $this->assertDispatchFails(
            $fixture['decision'],
            'Selected Routing Decisions require a selected Employee before dispatch.',
        );
        $this->assertSame(0, Assignment::query()->count());
    }

    public function test_work_request_must_be_routed_before_dispatch(): void
    {
        $fixture = $this->dispatchFixture(workRequestAttributes: [
            'status' => 'pending',
            'routed_at' => null,
        ]);

        $this->assertDispatchFails(
            $fixture['decision'],
            'Only routed Work Requests can be dispatched.',
        );
        $this->assertSame(0, Assignment::query()->count());
    }

    public function test_already_dispatched_work_request_is_rejected(): void
    {
        $fixture = $this->dispatchFixture();
        $existingAssignment = Assignment::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['employee'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->create([
                'business_process_run_id' => $fixture['run']->id,
                'business_process_run_step_id' => $fixture['runStep']->id,
                'work_request_id' => $fixture['workRequest']->id,
                'routing_decision_id' => $fixture['decision']->id,
            ]);
        $fixture['workRequest']->update([
            'status' => 'assignment_created',
            'assignment_id' => $existingAssignment->id,
            'dispatched_at' => now(),
        ]);
        $fixture['decision']->update(['assignment_id' => $existingAssignment->id]);

        $this->assertDispatchFails(
            $fixture['decision'],
            'Work Request has already been dispatched.',
        );
        $this->assertSame(1, Assignment::query()->count());
    }

    /**
     * @param  array<string, mixed>  $workRequestAttributes
     * @param  array<string, mixed>  $decisionAttributes
     * @return array<string, mixed>
     */
    private function dispatchFixture(array $workRequestAttributes = [], array $decisionAttributes = []): array
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create(['name' => 'Research']);
        $capability = Capability::factory()->create([
            'name' => 'Research',
            'capability_key' => 'research',
            'status' => 'active',
        ]);
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
            ->create([
                'status' => 'running',
                'progress_percent' => 0,
            ]);
        $runStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($run)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'status' => 'ready',
                'assignment_id' => null,
            ]);
        $run->update(['current_run_step_id' => $runStep->id]);
        $employee = Employee::factory()
            ->for($organization)
            ->for($department)
            ->create([
                'full_name' => 'Anna Research',
                'employment_status' => 'active',
                'paused_at' => null,
                'retired_at' => null,
                'archived_at' => null,
            ]);
        EmployeeCapability::factory()
            ->for($organization)
            ->for($employee)
            ->for($capability)
            ->create(['status' => 'active']);
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
            ->create(array_merge([
                'title' => 'Prepare research package',
                'assignment_type' => 'business_process_step',
                'priority' => 'high',
                'status' => 'routed',
                'routing_strategy' => 'first_available',
                'briefing' => [
                    'summary' => 'Research the assigned editorial package.',
                    'source' => 'process',
                ],
                'expected_output' => 'Research notes with sources.',
                'input_payload' => [
                    'topic' => 'AI adoption',
                    'locale' => 'en',
                ],
                'review_required' => true,
                'review_path' => 'Editor Review',
                'due_at' => now()->addHours(4),
                'assignment_id' => null,
                'routed_at' => now(),
                'dispatched_at' => null,
            ], $workRequestAttributes));
        $decision = RoutingDecision::factory()
            ->for($organization)
            ->for($workRequest)
            ->for($department)
            ->for($site)
            ->for($employee, 'selectedEmployee')
            ->create(array_merge([
                'status' => 'selected',
                'strategy' => 'first_available',
                'candidate_count' => 1,
                'eligible_count' => 1,
                'selected_employee_id' => $employee->id,
                'assignment_id' => null,
                'decision_reason' => 'Selected first eligible Employee in stable order.',
                'failure_reason' => null,
            ], $decisionAttributes));

        return [
            'organization' => $organization,
            'site' => $site,
            'department' => $department,
            'capability' => $capability,
            'sop' => $sop,
            'definition' => $definition,
            'step' => $step,
            'template' => $template,
            'run' => $run->refresh(),
            'runStep' => $runStep,
            'employee' => $employee,
            'workRequest' => $workRequest,
            'decision' => $decision,
        ];
    }

    private function assertDispatchFails(RoutingDecision $decision, string $message): void
    {
        try {
            app(AssignmentDispatchService::class)->dispatch($decision);
            $this->fail('Assignment dispatch did not fail.');
        } catch (DomainException $exception) {
            $this->assertSame($message, $exception->getMessage());
        }
    }
}
