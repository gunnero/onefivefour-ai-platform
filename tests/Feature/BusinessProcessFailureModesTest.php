<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use App\Services\AssignmentLifecycleService;
use App\Services\BusinessProcess\AssignmentDispatchService;
use App\Services\BusinessProcess\ProcessRunService;
use App\Services\BusinessProcess\WorkRequestCancellationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProcessFailureModesTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_assignment_blocks_linked_run_step_and_process_run_with_side_effects(): void
    {
        $fixture = $this->linkedAssignmentFixture();

        app(AssignmentLifecycleService::class)->block(
            $fixture['assignment'],
            escalationRequired: true,
            reason: 'Source access is unavailable.',
        );

        $runStep = $fixture['runStep']->refresh();
        $run = $fixture['run']->refresh();

        $this->assertSame('blocked', $fixture['assignment']->refresh()->status);
        $this->assertSame('blocked', $runStep->status);
        $this->assertNotNull($runStep->blocked_at);
        $this->assertSame('Source access is unavailable.', $runStep->blocked_reason);
        $this->assertSame('blocked', $run->status);
        $this->assertNotNull($run->blocked_at);
        $this->assertSame('Source access is unavailable.', $run->metadata['blocked_reason']);

        $this->assertProcessSideEffects(
            $fixture['organization'],
            $run,
            $runStep,
            $fixture['assignment'],
            'run_step_blocked',
        );
        $this->assertProcessSideEffects(
            $fixture['organization'],
            $run,
            null,
            $fixture['assignment'],
            'process_run_blocked',
        );
    }

    public function test_failed_assignment_fails_linked_run_step_and_process_run_with_side_effects(): void
    {
        $fixture = $this->linkedAssignmentFixture();

        app(AssignmentLifecycleService::class)->fail(
            $fixture['assignment'],
            reason: 'Fact check could not be completed.',
        );

        $runStep = $fixture['runStep']->refresh();
        $run = $fixture['run']->refresh();

        $this->assertSame('failed', $fixture['assignment']->refresh()->status);
        $this->assertSame('failed', $runStep->status);
        $this->assertNotNull($runStep->failed_at);
        $this->assertSame('Fact check could not be completed.', $runStep->failure_reason);
        $this->assertSame('failed', $run->status);
        $this->assertNotNull($run->failed_at);
        $this->assertSame('Fact check could not be completed.', $run->metadata['failure_reason']);

        $this->assertProcessSideEffects(
            $fixture['organization'],
            $run,
            $runStep,
            $fixture['assignment'],
            'run_step_failed',
        );
        $this->assertProcessSideEffects(
            $fixture['organization'],
            $run,
            null,
            $fixture['assignment'],
            'process_run_failed',
        );
    }

    public function test_work_request_can_be_cancelled_before_dispatch_and_does_not_create_assignment(): void
    {
        $fixture = $this->workRequestFixture(status: 'routed');
        $pendingDecision = $this->routingDecision($fixture, 'pending');
        $evaluatingDecision = $this->routingDecision($fixture, 'evaluating');
        $selectedDecision = $this->routingDecision($fixture, 'selected');

        $cancelled = app(WorkRequestCancellationService::class)->cancel(
            $fixture['workRequest'],
            'Editorial brief was withdrawn.',
        );

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertSame('Editorial brief was withdrawn.', $cancelled->metadata['cancellation_reason']);
        $this->assertSame('cancelled', $pendingDecision->refresh()->status);
        $this->assertSame('cancelled', $evaluatingDecision->refresh()->status);
        $this->assertSame('selected', $selectedDecision->refresh()->status);
        $this->assertSame(0, Assignment::query()->count());

        try {
            app(AssignmentDispatchService::class)->dispatch($selectedDecision->refresh());
            $this->fail('Cancelled Work Request dispatched an Assignment.');
        } catch (DomainException $exception) {
            $this->assertSame('Only routed Work Requests can be dispatched.', $exception->getMessage());
        }

        $this->assertSame(0, Assignment::query()->count());
        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $fixture['run']->id,
            'business_process_run_step_id' => $fixture['runStep']->id,
            'event_type' => 'work_request_cancelled',
        ]);
        $this->assertDatabaseHas('process_logs', [
            'business_process_run_id' => $fixture['run']->id,
            'business_process_run_step_id' => $fixture['runStep']->id,
            'log_level' => 'info',
        ]);
        $this->assertDatabaseHas('activities', [
            'organization_id' => $fixture['organization']->id,
            'activity_type' => 'work_request_cancelled',
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $fixture['organization']->id,
            'auditable_type' => WorkRequest::class,
            'auditable_id' => $fixture['workRequest']->id,
            'event_type' => 'work_request_cancelled',
        ]);
    }

    public function test_process_run_cancellation_cancels_eligible_runtime_records_and_preserves_completed_assignments(): void
    {
        $fixture = $this->cancellableProcessRunFixture();

        $cancelledRun = app(ProcessRunService::class)->cancel(
            $fixture['run'],
            'Client cancelled the package.',
        );

        $this->assertSame('cancelled', $cancelledRun->status);
        $this->assertNotNull($cancelledRun->cancelled_at);
        $this->assertSame('Client cancelled the package.', $cancelledRun->metadata['cancellation_reason']);
        $this->assertSame('cancelled', $fixture['readyRunStep']->refresh()->status);
        $this->assertSame('cancelled', $fixture['waitingRunStep']->refresh()->status);
        $this->assertSame('cancelled', $fixture['blockedRunStep']->refresh()->status);
        $this->assertSame('cancelled', $fixture['assignmentRunStep']->refresh()->status);
        $this->assertSame('completed', $fixture['completedRunStep']->refresh()->status);
        $this->assertSame('cancelled', $fixture['activeAssignment']->refresh()->status);
        $this->assertSame('completed', $fixture['completedAssignment']->refresh()->status);
        $this->assertSame('cancelled', $fixture['pendingWorkRequest']->refresh()->status);
        $this->assertSame('cancelled', $fixture['routedWorkRequest']->refresh()->status);
        $this->assertSame('cancelled', $fixture['pendingDecision']->refresh()->status);
        $this->assertSame('cancelled', $fixture['selectedDecision']->refresh()->status);

        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $cancelledRun->id,
            'event_type' => 'process_run_cancelled',
        ]);
        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $cancelledRun->id,
            'business_process_run_step_id' => $fixture['readyRunStep']->id,
            'event_type' => 'run_step_cancelled',
        ]);
        $this->assertDatabaseHas('activities', [
            'organization_id' => $fixture['organization']->id,
            'activity_type' => 'process_run_cancelled',
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $fixture['organization']->id,
            'auditable_type' => BusinessProcessRun::class,
            'auditable_id' => $cancelledRun->id,
            'event_type' => 'process_run_cancelled',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function linkedAssignmentFixture(): array
    {
        $fixture = $this->processFixture();
        $assignment = Assignment::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['employee'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->create([
                'status' => 'in_progress',
                'business_process_run_id' => $fixture['run']->id,
                'business_process_run_step_id' => $fixture['runStep']->id,
            ]);
        $fixture['runStep']->update(['assignment_id' => $assignment->id]);

        return [
            ...$fixture,
            'assignment' => $assignment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workRequestFixture(string $status = 'pending'): array
    {
        $fixture = $this->processFixture();
        $workRequest = WorkRequest::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['definition'])
            ->for($fixture['run'])
            ->for($fixture['runStep'], 'businessProcessRunStep')
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->for($fixture['capability'], 'requiredCapability')
            ->create([
                'status' => $status,
                'assignment_id' => null,
                'routed_at' => $status === 'routed' ? now() : null,
                'cancelled_at' => null,
                'metadata' => [],
            ]);

        return [
            ...$fixture,
            'workRequest' => $workRequest,
        ];
    }

    private function routingDecision(array $fixture, string $status): RoutingDecision
    {
        return RoutingDecision::factory()
            ->for($fixture['organization'])
            ->for($fixture['workRequest'])
            ->for($fixture['department'])
            ->for($fixture['site'])
            ->for($fixture['employee'], 'selectedEmployee')
            ->create([
                'status' => $status,
                'selected_employee_id' => $status === 'selected' ? $fixture['employee']->id : null,
                'assignment_id' => null,
                'failure_reason' => null,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cancellableProcessRunFixture(): array
    {
        $fixture = $this->processFixture();
        $readyRunStep = $fixture['runStep'];
        $waitingRunStep = $this->runStep($fixture, 'waiting_for_dependency', 'writing', 2);
        $blockedRunStep = $this->runStep($fixture, 'blocked', 'seo', 3);
        $assignmentRunStep = $this->runStep($fixture, 'assignment_created', 'delivery-ready', 4);
        $completedRunStep = $this->runStep($fixture, 'completed', 'human-approval', 5);
        $activeAssignment = Assignment::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['employee'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->create([
                'status' => 'in_progress',
                'business_process_run_id' => $fixture['run']->id,
                'business_process_run_step_id' => $assignmentRunStep->id,
            ]);
        $completedAssignment = Assignment::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['employee'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->create([
                'status' => 'completed',
                'business_process_run_id' => $fixture['run']->id,
                'business_process_run_step_id' => $completedRunStep->id,
                'completed_at' => now()->subHour(),
            ]);
        $assignmentRunStep->update(['assignment_id' => $activeAssignment->id]);
        $completedRunStep->update(['assignment_id' => $completedAssignment->id]);
        $pendingWorkRequestFixture = $this->workRequestForRunStep($fixture, $waitingRunStep, 'pending');
        $routedWorkRequestFixture = $this->workRequestForRunStep($fixture, $assignmentRunStep, 'routed');
        $pendingDecision = $this->routingDecision($pendingWorkRequestFixture, 'pending');
        $selectedDecision = $this->routingDecision($routedWorkRequestFixture, 'selected');

        return [
            ...$fixture,
            'readyRunStep' => $readyRunStep,
            'waitingRunStep' => $waitingRunStep,
            'blockedRunStep' => $blockedRunStep,
            'assignmentRunStep' => $assignmentRunStep,
            'completedRunStep' => $completedRunStep,
            'activeAssignment' => $activeAssignment,
            'completedAssignment' => $completedAssignment,
            'pendingWorkRequest' => $pendingWorkRequestFixture['workRequest'],
            'routedWorkRequest' => $routedWorkRequestFixture['workRequest'],
            'pendingDecision' => $pendingDecision,
            'selectedDecision' => $selectedDecision,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workRequestForRunStep(array $fixture, BusinessProcessRunStep $runStep, string $status): array
    {
        $workRequest = WorkRequest::factory()
            ->for($fixture['organization'])
            ->for($fixture['site'])
            ->for($fixture['department'])
            ->for($fixture['definition'])
            ->for($fixture['run'])
            ->for($runStep, 'businessProcessRunStep')
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->for($fixture['capability'], 'requiredCapability')
            ->create([
                'status' => $status,
                'assignment_id' => null,
                'routed_at' => $status === 'routed' ? now() : null,
                'cancelled_at' => null,
                'metadata' => [],
            ]);

        return [
            ...$fixture,
            'workRequest' => $workRequest,
        ];
    }

    private function runStep(array $fixture, string $status, string $stepKey, int $sortOrder): BusinessProcessRunStep
    {
        $step = BusinessProcessStep::factory()
            ->for($fixture['organization'])
            ->for($fixture['definition'])
            ->for($fixture['department'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->for($fixture['capability'], 'requiredCapability')
            ->create([
                'step_key' => $stepKey,
                'name' => str_replace('-', ' ', $stepKey),
                'sort_order' => $sortOrder,
                'dependency_rules' => [],
            ]);

        return BusinessProcessRunStep::factory()
            ->for($fixture['organization'])
            ->for($fixture['run'])
            ->for($step, 'businessProcessStep')
            ->for($fixture['department'])
            ->for($fixture['sop'], 'standardOperatingProcedure')
            ->for($fixture['capability'], 'requiredCapability')
            ->create([
                'status' => $status,
                'sort_order' => $sortOrder,
                'assignment_id' => null,
                'blocked_at' => $status === 'blocked' ? now()->subHour() : null,
                'completed_at' => $status === 'completed' ? now()->subHour() : null,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function processFixture(): array
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create(['name' => 'Operations']);
        $capability = Capability::factory()->create([
            'name' => 'Operations',
            'capability_key' => 'operations',
            'status' => 'active',
        ]);
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();
        $employee = Employee::factory()
            ->for($organization)
            ->for($department)
            ->create(['employment_status' => 'active']);
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
                'step_key' => 'research',
                'name' => 'Research',
                'sort_order' => 1,
                'dependency_rules' => [],
            ]);
        $run = BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create([
                'status' => 'running',
                'progress_percent' => 0,
                'metadata' => [],
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
                'sort_order' => 1,
                'assignment_id' => null,
            ]);
        $run->update(['current_run_step_id' => $runStep->id]);

        return [
            'organization' => $organization,
            'site' => $site,
            'department' => $department,
            'capability' => $capability,
            'sop' => $sop,
            'employee' => $employee,
            'definition' => $definition,
            'step' => $step,
            'run' => $run->refresh(),
            'runStep' => $runStep,
        ];
    }

    private function assertProcessSideEffects(
        Organization $organization,
        BusinessProcessRun $run,
        ?BusinessProcessRunStep $runStep,
        Assignment $assignment,
        string $eventType,
    ): void {
        $event = ProcessEvent::query()
            ->where('organization_id', $organization->id)
            ->where('business_process_run_id', $run->id)
            ->where('event_type', $eventType)
            ->first();

        $this->assertNotNull($event);
        $this->assertSame($runStep?->id, $event->business_process_run_step_id);
        $this->assertSame($assignment->id, $event->assignment_id);

        $this->assertDatabaseHas('process_logs', [
            'organization_id' => $organization->id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $runStep?->id,
            'process_event_id' => $event->id,
            'assignment_id' => $assignment->id,
            'log_level' => 'info',
        ]);
        $this->assertDatabaseHas('activities', [
            'organization_id' => $organization->id,
            'activity_type' => $eventType,
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $organization->id,
            'event_type' => $eventType,
        ]);
    }
}
