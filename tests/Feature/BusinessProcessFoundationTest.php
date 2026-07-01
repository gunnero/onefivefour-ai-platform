<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentTemplate;
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
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BusinessProcessFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sprint_002_schema_exists_on_postgresql(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());

        foreach ([
            'business_process_definitions',
            'business_process_steps',
            'assignment_templates',
            'business_process_runs',
            'business_process_run_steps',
            'process_events',
            'process_logs',
            'work_requests',
            'routing_decisions',
            'department_queues',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table exists.");
            $this->assertTrue(Schema::hasColumn($table, 'organization_id'), "{$table} is organization-scoped.");
        }

        foreach ([
            'business_process_run_id',
            'business_process_run_step_id',
            'work_request_id',
            'routing_decision_id',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('assignments', $column), "assignments.{$column} exists.");
        }

        $this->assertTrue(Schema::hasColumn('business_process_steps', 'dependency_rules'));
        $this->assertTrue(Schema::hasColumn('assignment_templates', 'briefing_template'));
        $this->assertTrue(Schema::hasColumn('routing_decisions', 'candidate_snapshot'));
    }

    public function test_factories_can_create_sprint_002_relationship_graph(): void
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create(['name' => 'Research']);
        $employee = Employee::factory()->for($organization)->for($department)->create();
        $capability = Capability::factory()->create(['name' => 'Research', 'capability_key' => 'research']);
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();

        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->for($department, 'owningDepartment')
            ->for($employee, 'manager')
            ->create();

        $step = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create();

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
            ->create();

        $runStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($run)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create();

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
            ->create();

        $routingDecision = RoutingDecision::factory()
            ->for($organization)
            ->for($workRequest)
            ->for($department)
            ->for($site)
            ->for($employee, 'selectedEmployee')
            ->create();

        $assignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create([
                'business_process_run_id' => $run->id,
                'business_process_run_step_id' => $runStep->id,
                'work_request_id' => $workRequest->id,
                'routing_decision_id' => $routingDecision->id,
            ]);

        $run->update(['current_run_step_id' => $runStep->id]);
        $runStep->update(['assignment_id' => $assignment->id]);
        $workRequest->update(['assignment_id' => $assignment->id]);
        $routingDecision->update(['assignment_id' => $assignment->id]);

        $event = ProcessEvent::factory()
            ->for($organization)
            ->for($definition)
            ->for($run)
            ->for($runStep, 'businessProcessRunStep')
            ->for($assignment)
            ->create();

        $log = ProcessLog::factory()
            ->for($organization)
            ->for($run)
            ->for($runStep, 'businessProcessRunStep')
            ->for($event, 'processEvent')
            ->for($assignment)
            ->create();

        $queue = DepartmentQueue::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->for($employee, 'lastSelectedEmployee')
            ->create();

        $this->assertTrue($organization->businessProcessDefinitions->contains($definition));
        $this->assertTrue($definition->steps->contains($step));
        $this->assertTrue($definition->assignmentTemplates->contains($template));
        $this->assertTrue($definition->runs->contains($run));
        $this->assertTrue($step->assignmentTemplates->contains($template));
        $this->assertTrue($run->runSteps->contains($runStep));
        $this->assertTrue($run->currentRunStep->is($runStep));
        $this->assertTrue($runStep->workRequests->contains($workRequest));
        $this->assertTrue($workRequest->routingDecisions->contains($routingDecision));
        $this->assertTrue($workRequest->assignment->is($assignment));
        $this->assertTrue($assignment->businessProcessRun->is($run));
        $this->assertTrue($assignment->businessProcessRunStep->is($runStep));
        $this->assertTrue($assignment->workRequest->is($workRequest));
        $this->assertTrue($assignment->routingDecision->is($routingDecision));
        $this->assertTrue($event->processLogs->contains($log));
        $this->assertTrue($department->departmentQueues->contains($queue));
        $this->assertTrue($capability->assignmentTemplates->contains($template));
    }

    public function test_database_seeder_creates_prepare_editorial_package_process(): void
    {
        $this->seed();

        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $definition = BusinessProcessDefinition::query()
            ->where('organization_id', $organization->id)
            ->where('process_key', 'prepare-editorial-package')
            ->where('version', 1)
            ->firstOrFail();

        $this->assertSame('Prepare Editorial Package', $definition->name);
        $this->assertSame('active', $definition->status);
        $this->assertSame(8, $definition->steps()->count());
        $this->assertSame(8, $definition->assignmentTemplates()->count());

        $expectedSteps = [
            'Research' => ['Research', 'Research'],
            'Writing' => ['Writing', 'Writing'],
            'Localization' => ['Localization', 'Localization'],
            'Fact Check' => ['Trust & Safety', 'Fact Checking'],
            'SEO' => ['SEO', 'SEO'],
            'Editor Review' => ['Editorial', 'Editing'],
            'Human Approval' => ['Editorial', 'Editing'],
            'Delivery Ready' => ['Operations', 'Editing'],
        ];

        foreach ($expectedSteps as $stepName => [$departmentName, $capabilityName]) {
            $step = $definition->steps()->where('name', $stepName)->first();

            $this->assertNotNull($step, "{$stepName} step exists.");
            $this->assertSame($departmentName, $step->department->name);
            $this->assertSame($capabilityName, $step->requiredCapability->name);
            $this->assertTrue($step->assignmentTemplates()->exists(), "{$stepName} has an Assignment Template.");
        }

        foreach (['Research', 'Writing', 'Localization', 'Trust & Safety', 'SEO', 'Editorial', 'Operations'] as $departmentName) {
            $department = $organization->departments()->where('name', $departmentName)->firstOrFail();

            $this->assertTrue(
                DepartmentQueue::query()
                    ->where('organization_id', $organization->id)
                    ->where('department_id', $department->id)
                    ->exists(),
                "{$departmentName} Department Queue is seeded.",
            );
        }
    }
}
