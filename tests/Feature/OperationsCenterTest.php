<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\DepartmentQueue;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\User;
use App\Models\WorkRequest;
use App\Services\OperationsCenter\OperationsCenterProjection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_operations_center_data_appears_on_hq_dashboard(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@onefivefour.ai')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Operations Center')
            ->assertSeeText('Quick Stats')
            ->assertSeeText('Active Business Processes')
            ->assertSeeText('Prepare Editorial Package')
            ->assertSeeText('Department Queues')
            ->assertSeeText('Research Queue')
            ->assertSeeText('Operations Feed')
            ->assertSeeText('Activity Feed');
    }

    public function test_operations_center_projection_counts_runtime_state(): void
    {
        $fixture = $this->operationsFixture();

        $projection = app(OperationsCenterProjection::class)->forOrganization($fixture['organization']);

        $this->assertSame(1, $projection['quickStats']['running_processes']);
        $this->assertSame(1, $projection['quickStats']['ready_steps']);
        $this->assertSame(1, $projection['quickStats']['pending_work_requests']);
        $this->assertSame(1, $projection['quickStats']['assignments']);
        $this->assertSame(1, $projection['quickStats']['blocked_runs']);
        $this->assertSame(1, $projection['quickStats']['failed_runs']);
        $this->assertSame(1, $projection['quickStats']['waiting_approval']);

        $queue = $projection['departmentQueues']->first();

        $this->assertSame('Operations Queue', $queue->name);
        $this->assertSame(1, $queue->pending_work_requests);
        $this->assertSame(1, $queue->routed_work_requests);
        $this->assertSame(1, $queue->blocked_work_requests);
        $this->assertSame(1, $queue->failed_work_requests);

        $this->assertSame(1, $projection['workRequestCounts']['pending']);
        $this->assertSame(1, $projection['workRequestCounts']['routed']);
        $this->assertSame(1, $projection['workRequestCounts']['waiting_for_manual_selection']);
        $this->assertSame(1, $projection['workRequestCounts']['blocked']);
        $this->assertSame(1, $projection['workRequestCounts']['cancelled']);

        $this->assertSame('process_event', $projection['operationsFeed']->first()['source']);
        $this->assertSame('activity', $projection['operationsFeed']->get(1)['source']);
    }

    public function test_operations_center_loads_runtime_records_on_hq_dashboard(): void
    {
        $fixture = $this->operationsFixture();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('Operations Center')
            ->assertSeeText('Running Processes')
            ->assertSeeText('Ready Steps')
            ->assertSeeText('Pending Work Requests')
            ->assertSeeText('Assignments')
            ->assertSeeText('Blocked Runs')
            ->assertSeeText('Failed Runs')
            ->assertSeeText('Waiting Approval')
            ->assertSeeText('Active Business Processes')
            ->assertSeeText('Operations Process')
            ->assertSeeText('Current Process Runs')
            ->assertSeeText('Current Operations Run')
            ->assertSeeText('Operations')
            ->assertSeeText('Ops AI')
            ->assertSeeText('Operations center assignment')
            ->assertSeeText('Department Queues')
            ->assertSeeText('Operations Queue')
            ->assertSeeText('Work Requests')
            ->assertSeeText('Waiting Manual')
            ->assertSeeText('Routing Decisions')
            ->assertSeeText('first_available')
            ->assertSeeText('Selected first eligible Employee in stable order.')
            ->assertSeeText('No eligible Employee matched the Work Request.')
            ->assertSeeText('Operations Feed')
            ->assertSeeText('Process Event')
            ->assertSeeText('Activity')
            ->assertSeeText('Runtime process event')
            ->assertSeeText('Runtime activity');

        $this->assertSame('Current Operations Run', $fixture['runningRun']->title);
    }

    /**
     * @return array<string, mixed>
     */
    private function operationsFixture(): array
    {
        $organization = Organization::factory()->create([
            'name' => 'Operations Org',
            'slug' => 'onefivefour',
            'timezone' => 'Europe/Skopje',
        ]);
        $site = Site::factory()->for($organization)->create(['name' => 'Operations Site']);
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
            ->create([
                'full_name' => 'Ops AI',
                'employment_status' => 'active',
            ]);
        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->for($department, 'owningDepartment')
            ->for($employee, 'manager')
            ->create([
                'name' => 'Operations Process',
                'status' => 'active',
                'activated_at' => now()->subDay(),
            ]);
        $step = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'name' => 'Current Operations Step',
                'step_key' => 'current-operations-step',
                'sort_order' => 1,
                'dependency_rules' => [],
            ]);
        $runningRun = BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create([
                'title' => 'Current Operations Run',
                'status' => 'running',
                'progress_percent' => 33,
                'started_at' => now()->subHours(2),
            ]);
        BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create(['status' => 'blocked', 'blocked_at' => now()->subHour()]);
        BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create(['status' => 'failed', 'failed_at' => now()->subHour()]);
        BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create(['status' => 'waiting_for_approval']);
        $runStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($runningRun)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'status' => 'ready',
                'sort_order' => 1,
            ]);
        $assignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create([
                'title' => 'Operations center assignment',
                'status' => 'in_progress',
                'business_process_run_id' => $runningRun->id,
                'business_process_run_step_id' => $runStep->id,
            ]);
        $runStep->update(['assignment_id' => $assignment->id]);
        $runningRun->update(['current_run_step_id' => $runStep->id]);
        DepartmentQueue::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->for($employee, 'lastSelectedEmployee')
            ->create([
                'name' => 'Operations Queue',
                'queue_key' => 'operations-queue',
                'status' => 'active',
            ]);
        $pendingWorkRequest = $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'pending');
        $routedWorkRequest = $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'routed');
        $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'waiting_for_manual_selection');
        $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'blocked');
        $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'failed');
        $this->workRequest($organization, $site, $department, $definition, $runningRun, $runStep, $sop, $capability, 'cancelled');

        RoutingDecision::factory()
            ->for($organization)
            ->for($routedWorkRequest)
            ->for($department)
            ->for($site)
            ->for($employee, 'selectedEmployee')
            ->create([
                'strategy' => 'first_available',
                'status' => 'selected',
                'selected_employee_id' => $employee->id,
                'decision_reason' => 'Selected first eligible Employee in stable order.',
            ]);
        RoutingDecision::factory()
            ->for($organization)
            ->for($pendingWorkRequest)
            ->for($department)
            ->for($site)
            ->create([
                'strategy' => 'first_available',
                'status' => 'no_eligible_employee',
                'selected_employee_id' => null,
                'failure_reason' => 'No eligible Employee matched the Work Request.',
            ]);
        ProcessEvent::factory()
            ->for($organization)
            ->for($definition, 'businessProcessDefinition')
            ->for($runningRun, 'businessProcessRun')
            ->for($runStep, 'businessProcessRunStep')
            ->for($assignment)
            ->create([
                'event_type' => 'run_step_ready',
                'summary' => 'Runtime process event',
                'occurred_at' => now(),
                'created_at' => now(),
            ]);
        Activity::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($assignment)
            ->create([
                'audit_log_id' => null,
                'activity_type' => 'assignment_status_changed',
                'status' => 'visible',
                'title' => 'Runtime activity',
                'occurred_at' => now()->subMinute(),
            ]);

        return [
            'organization' => $organization,
            'runningRun' => $runningRun->refresh(),
        ];
    }

    private function workRequest(
        Organization $organization,
        Site $site,
        Department $department,
        BusinessProcessDefinition $definition,
        BusinessProcessRun $run,
        BusinessProcessRunStep $runStep,
        StandardOperatingProcedure $sop,
        Capability $capability,
        string $status,
    ): WorkRequest {
        return WorkRequest::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($definition)
            ->for($run)
            ->for($runStep, 'businessProcessRunStep')
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'assignment_template_id' => null,
                'title' => "Operations {$status} Work Request",
                'status' => $status,
                'routed_at' => $status === 'routed' ? now() : null,
                'blocked_at' => $status === 'blocked' ? now() : null,
                'failed_at' => $status === 'failed' ? now() : null,
                'cancelled_at' => $status === 'cancelled' ? now() : null,
            ]);
    }
}
