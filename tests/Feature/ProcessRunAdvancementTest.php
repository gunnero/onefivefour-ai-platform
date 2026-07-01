<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use App\Services\AssignmentLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessRunAdvancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_first_process_assignment_completes_run_step_and_marks_dependent_step_ready(): void
    {
        $fixture = $this->twoStepProcessFixture();
        $outputPayload = [
            'summary' => 'Research notes with source links.',
            'sources' => ['https://example.com/report'],
        ];

        app(AssignmentLifecycleService::class)->complete($fixture['researchAssignment'], $outputPayload);

        $researchRunStep = $fixture['researchRunStep']->refresh();
        $writingRunStep = $fixture['writingRunStep']->refresh();
        $run = $fixture['run']->refresh();

        $this->assertSame('completed', $researchRunStep->status);
        $this->assertSame($outputPayload, $researchRunStep->output_payload);
        $this->assertNotNull($researchRunStep->completed_at);
        $this->assertSame('ready', $writingRunStep->status);
        $this->assertNotNull($writingRunStep->ready_at);
        $this->assertSame('running', $run->status);
        $this->assertSame($writingRunStep->id, $run->current_run_step_id);
        $this->assertSame(0, WorkRequest::query()->count());

        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $researchRunStep->id,
            'assignment_id' => $fixture['researchAssignment']->id,
            'event_type' => 'run_step_completed',
        ]);
        $this->assertDatabaseHas('process_events', [
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $writingRunStep->id,
            'event_type' => 'run_step_ready',
        ]);

        $this->assertGreaterThanOrEqual(
            2,
            ProcessLog::query()
                ->where('business_process_run_id', $run->id)
                ->whereIn('business_process_run_step_id', [$researchRunStep->id, $writingRunStep->id])
                ->whereNotNull('process_event_id')
                ->count(),
        );

        $this->assertDatabaseHas('activities', [
            'organization_id' => $fixture['organization']->id,
            'activity_type' => 'run_step_completed',
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('activities', [
            'organization_id' => $fixture['organization']->id,
            'activity_type' => 'run_step_ready',
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $fixture['organization']->id,
            'auditable_type' => BusinessProcessRunStep::class,
            'auditable_id' => $researchRunStep->id,
            'event_type' => 'run_step_completed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $fixture['organization']->id,
            'auditable_type' => BusinessProcessRunStep::class,
            'auditable_id' => $writingRunStep->id,
            'event_type' => 'run_step_ready',
        ]);
    }

    public function test_final_assignment_completion_completes_process_run(): void
    {
        $fixture = $this->twoStepProcessFixture(firstStepCompleted: true);
        $outputPayload = [
            'summary' => 'Final delivery package is ready.',
        ];

        app(AssignmentLifecycleService::class)->complete($fixture['writingAssignment'], $outputPayload);

        $writingRunStep = $fixture['writingRunStep']->refresh();
        $run = $fixture['run']->refresh();

        $this->assertSame('completed', $writingRunStep->status);
        $this->assertSame($outputPayload, $writingRunStep->output_payload);
        $this->assertNotNull($writingRunStep->completed_at);
        $this->assertSame('completed', $run->status);
        $this->assertNotNull($run->completed_at);
        $this->assertSame(100, $run->progress_percent);
        $this->assertSame(0, WorkRequest::query()->count());

        $event = ProcessEvent::query()
            ->where('business_process_run_id', $run->id)
            ->where('event_type', 'process_run_completed')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame($fixture['writingAssignment']->id, $event->assignment_id);

        $this->assertDatabaseHas('process_logs', [
            'business_process_run_id' => $run->id,
            'process_event_id' => $event->id,
            'assignment_id' => $fixture['writingAssignment']->id,
            'log_level' => 'info',
        ]);
        $this->assertDatabaseHas('activities', [
            'organization_id' => $fixture['organization']->id,
            'activity_type' => 'process_run_completed',
            'status' => 'visible',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $fixture['organization']->id,
            'auditable_type' => BusinessProcessRun::class,
            'auditable_id' => $run->id,
            'event_type' => 'process_run_completed',
        ]);
    }

    public function test_non_process_assignment_completion_does_not_affect_process_runs(): void
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $department = Department::factory()->for($organization)->create();
        $employee = Employee::factory()->for($organization)->for($department)->create();
        $sop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($department)
            ->for($site)
            ->create();
        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->create(['status' => 'active']);
        $run = BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create(['status' => 'running', 'progress_percent' => 0]);
        $assignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($department)
            ->for($employee)
            ->for($sop, 'standardOperatingProcedure')
            ->create([
                'status' => 'in_progress',
                'business_process_run_id' => null,
                'business_process_run_step_id' => null,
            ]);

        app(AssignmentLifecycleService::class)->complete($assignment, ['summary' => 'Manual work completed.']);

        $this->assertSame('completed', $assignment->refresh()->status);
        $this->assertSame('running', $run->refresh()->status);
        $this->assertNull($run->completed_at);
        $this->assertSame(0, $run->progress_percent);
        $this->assertSame(0, ProcessEvent::query()->count());
        $this->assertSame(0, ProcessLog::query()->count());
        $this->assertSame(0, Activity::query()->whereIn('activity_type', [
            'run_step_completed',
            'run_step_ready',
            'process_run_completed',
        ])->count());
        $this->assertSame(0, AuditLog::query()->whereIn('event_type', [
            'run_step_completed',
            'run_step_ready',
            'process_run_completed',
        ])->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function twoStepProcessFixture(bool $firstStepCompleted = false): array
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $researchDepartment = Department::factory()->for($organization)->create(['name' => 'Research']);
        $writingDepartment = Department::factory()->for($organization)->create(['name' => 'Writing']);
        $researchCapability = Capability::factory()->create([
            'name' => 'Research',
            'capability_key' => 'research',
            'status' => 'active',
        ]);
        $writingCapability = Capability::factory()->create([
            'name' => 'Writing',
            'capability_key' => 'writing',
            'status' => 'active',
        ]);
        $researchSop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($researchDepartment)
            ->for($site)
            ->create();
        $writingSop = StandardOperatingProcedure::factory()
            ->for($organization)
            ->for($writingDepartment)
            ->for($site)
            ->create();
        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->create(['status' => 'active']);
        $researchStep = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($researchDepartment, 'department')
            ->for($researchSop, 'standardOperatingProcedure')
            ->for($researchCapability, 'requiredCapability')
            ->create([
                'step_key' => 'research',
                'name' => 'Research',
                'sort_order' => 1,
                'dependency_rules' => [],
                'approval_required' => false,
            ]);
        $writingStep = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($writingDepartment, 'department')
            ->for($writingSop, 'standardOperatingProcedure')
            ->for($writingCapability, 'requiredCapability')
            ->create([
                'step_key' => 'writing',
                'name' => 'Writing',
                'sort_order' => 2,
                'dependency_rules' => [[
                    'step_key' => 'research',
                    'required_status' => 'completed',
                ]],
                'approval_required' => false,
            ]);
        $run = BusinessProcessRun::factory()
            ->for($organization)
            ->for($definition)
            ->for($site)
            ->create([
                'status' => 'running',
                'progress_percent' => $firstStepCompleted ? 50 : 0,
            ]);
        $researchRunStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($run)
            ->for($researchStep, 'businessProcessStep')
            ->for($researchDepartment, 'department')
            ->for($researchSop, 'standardOperatingProcedure')
            ->for($researchCapability, 'requiredCapability')
            ->create([
                'status' => $firstStepCompleted ? 'completed' : 'assignment_created',
                'sort_order' => 1,
                'assignment_id' => null,
                'employee_id' => null,
                'output_payload' => $firstStepCompleted ? ['summary' => 'Research complete.'] : null,
                'completed_at' => $firstStepCompleted ? now()->subHour() : null,
            ]);
        $writingRunStep = BusinessProcessRunStep::factory()
            ->for($organization)
            ->for($run)
            ->for($writingStep, 'businessProcessStep')
            ->for($writingDepartment, 'department')
            ->for($writingSop, 'standardOperatingProcedure')
            ->for($writingCapability, 'requiredCapability')
            ->create([
                'status' => $firstStepCompleted ? 'assignment_created' : 'waiting_for_dependency',
                'sort_order' => 2,
                'assignment_id' => null,
                'employee_id' => null,
                'ready_at' => $firstStepCompleted ? now()->subMinutes(30) : null,
            ]);
        $researchEmployee = Employee::factory()
            ->for($organization)
            ->for($researchDepartment)
            ->create(['employment_status' => 'active']);
        $writingEmployee = Employee::factory()
            ->for($organization)
            ->for($writingDepartment)
            ->create(['employment_status' => 'active']);
        $researchAssignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($researchDepartment, 'department')
            ->for($researchEmployee, 'employee')
            ->for($researchSop, 'standardOperatingProcedure')
            ->create([
                'status' => $firstStepCompleted ? 'completed' : 'in_progress',
                'business_process_run_id' => $run->id,
                'business_process_run_step_id' => $researchRunStep->id,
                'work_request_id' => null,
                'routing_decision_id' => null,
                'output_payload' => $firstStepCompleted ? ['summary' => 'Research complete.'] : null,
                'completed_at' => $firstStepCompleted ? now()->subHour() : null,
            ]);
        $writingAssignment = Assignment::factory()
            ->for($organization)
            ->for($site)
            ->for($writingDepartment, 'department')
            ->for($writingEmployee, 'employee')
            ->for($writingSop, 'standardOperatingProcedure')
            ->create([
                'status' => 'in_progress',
                'business_process_run_id' => $run->id,
                'business_process_run_step_id' => $writingRunStep->id,
                'work_request_id' => null,
                'routing_decision_id' => null,
            ]);

        $researchRunStep->update(['assignment_id' => $researchAssignment->id]);
        $writingRunStep->update(['assignment_id' => $writingAssignment->id]);
        $run->update([
            'current_run_step_id' => $firstStepCompleted ? $writingRunStep->id : $researchRunStep->id,
        ]);

        return [
            'organization' => $organization,
            'site' => $site,
            'definition' => $definition,
            'run' => $run->refresh(),
            'researchStep' => $researchStep,
            'writingStep' => $writingStep,
            'researchRunStep' => $researchRunStep,
            'writingRunStep' => $writingRunStep,
            'researchAssignment' => $researchAssignment,
            'writingAssignment' => $writingAssignment,
        ];
    }
}
