<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\Site;
use App\Services\BusinessProcess\ProcessRunService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessRunServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_inactive_process_definition_fails(): void
    {
        $definition = BusinessProcessDefinition::factory()->create([
            'status' => 'draft',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only active Business Process Definitions can start Process Runs.');

        app(ProcessRunService::class)->start(
            definition: $definition,
            title: 'Draft process should not run',
        );

        $this->assertDatabaseCount('business_process_runs', 0);
    }

    public function test_starting_active_process_definition_creates_run_steps_events_logs_activity_and_audit(): void
    {
        $organization = Organization::factory()->create();
        $site = Site::factory()->for($organization)->create();
        $definition = BusinessProcessDefinition::factory()
            ->for($organization)
            ->create([
                'status' => 'active',
                'name' => 'Prepare Editorial Package',
                'process_key' => 'prepare-editorial-package',
            ]);

        $researchDepartment = Department::factory()->for($organization)->create(['name' => 'Research']);
        $writingDepartment = Department::factory()->for($organization)->create(['name' => 'Writing']);
        $researchCapability = Capability::factory()->create(['name' => 'Research', 'capability_key' => 'research']);
        $writingCapability = Capability::factory()->create(['name' => 'Writing', 'capability_key' => 'writing']);

        $researchStep = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($researchDepartment)
            ->for($researchCapability, 'requiredCapability')
            ->create([
                'step_key' => 'research',
                'name' => 'Research',
                'status' => 'active',
                'sort_order' => 1,
                'dependency_rules' => [],
                'approval_required' => false,
                'expected_output' => 'Research package',
            ]);

        $writingStep = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($writingDepartment)
            ->for($writingCapability, 'requiredCapability')
            ->create([
                'step_key' => 'writing',
                'name' => 'Writing',
                'status' => 'active',
                'sort_order' => 2,
                'dependency_rules' => [
                    [
                        'step_key' => 'research',
                        'required_status' => 'completed',
                    ],
                ],
                'approval_required' => true,
                'expected_output' => 'Draft package',
            ]);

        BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($writingDepartment)
            ->for($writingCapability, 'requiredCapability')
            ->create([
                'step_key' => 'archived-step',
                'name' => 'Archived Step',
                'status' => 'retired',
                'sort_order' => 3,
                'dependency_rules' => [],
            ]);

        $run = app(ProcessRunService::class)->start(
            definition: $definition,
            site: $site,
            title: 'Prepare July editorial package',
            inputPayload: ['brief' => 'July package'],
            priority: 'high',
        );

        $this->assertInstanceOf(BusinessProcessRun::class, $run);
        $this->assertSame($organization->id, $run->organization_id);
        $this->assertSame($definition->id, $run->business_process_definition_id);
        $this->assertSame($site->id, $run->site_id);
        $this->assertSame('Prepare July editorial package', $run->title);
        $this->assertSame('running', $run->status);
        $this->assertSame('high', $run->priority);
        $this->assertSame(['brief' => 'July package'], $run->input_payload);
        $this->assertNotNull($run->run_key);
        $this->assertNotNull($run->started_at);

        $this->assertSame(2, $run->runSteps()->count());

        $researchRunStep = $run->runSteps()
            ->where('business_process_step_id', $researchStep->id)
            ->firstOrFail();
        $writingRunStep = $run->runSteps()
            ->where('business_process_step_id', $writingStep->id)
            ->firstOrFail();

        $this->assertSame('ready', $researchRunStep->status);
        $this->assertNotNull($researchRunStep->ready_at);
        $this->assertSame($researchDepartment->id, $researchRunStep->department_id);
        $this->assertSame($researchCapability->id, $researchRunStep->required_capability_id);
        $this->assertFalse($researchRunStep->approval_required);

        $this->assertSame('waiting_for_dependency', $writingRunStep->status);
        $this->assertNull($writingRunStep->ready_at);
        $this->assertSame($writingDepartment->id, $writingRunStep->department_id);
        $this->assertSame($writingCapability->id, $writingRunStep->required_capability_id);
        $this->assertTrue($writingRunStep->approval_required);

        $this->assertSame($researchRunStep->id, $run->refresh()->current_run_step_id);

        $this->assertDatabaseHas('process_events', [
            'organization_id' => $organization->id,
            'business_process_definition_id' => $definition->id,
            'business_process_run_id' => $run->id,
            'event_type' => 'process_run_started',
        ]);
        $this->assertDatabaseHas('process_events', [
            'organization_id' => $organization->id,
            'business_process_definition_id' => $definition->id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $researchRunStep->id,
            'event_type' => 'run_step_ready',
        ]);
        $this->assertSame(2, ProcessEvent::query()->where('business_process_run_id', $run->id)->count());

        $this->assertGreaterThanOrEqual(
            2,
            ProcessLog::query()->where('business_process_run_id', $run->id)->count(),
        );

        $activity = Activity::query()
            ->where('organization_id', $organization->id)
            ->where('activity_type', 'process_run_started')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($site->id, $activity->site_id);
        $this->assertSame('Process Run started: Prepare July editorial package', $activity->title);
        $this->assertSame($run->id, $activity->metadata['business_process_run_id']);

        $auditLog = AuditLog::query()
            ->where('organization_id', $organization->id)
            ->where('auditable_type', BusinessProcessRun::class)
            ->where('auditable_id', $run->id)
            ->where('event_type', 'process_run_started')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('started', $auditLog->action);
        $this->assertSame('running', $auditLog->after_state['status']);

        $this->assertSame(0, Assignment::query()->count());
    }
}
