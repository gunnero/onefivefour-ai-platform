<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AssignmentTemplate;
use App\Models\AuditLog;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;
use App\Services\BusinessProcess\ProcessRunService;
use App\Services\BusinessProcess\WorkRequestFactory as WorkRequestCreationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WorkRequestCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_ready_run_step_creates_work_request_and_records_side_effects(): void
    {
        Carbon::setTestNow('2026-07-01 12:00:00');

        [
            'organization' => $organization,
            'site' => $site,
            'definition' => $definition,
            'department' => $department,
            'capability' => $capability,
            'sop' => $sop,
            'template' => $template,
        ] = $this->businessProcessFixture();

        $run = app(ProcessRunService::class)->start(
            definition: $definition,
            site: $site,
            title: 'Prepare editorial package',
            inputPayload: ['brief' => 'July package'],
            priority: 'high',
        );
        $runStep = $run->runSteps()->where('status', 'ready')->firstOrFail();

        $workRequest = app(WorkRequestCreationService::class)->createFromRunStep($runStep);

        $this->assertInstanceOf(WorkRequest::class, $workRequest);
        $this->assertSame($organization->id, $workRequest->organization_id);
        $this->assertSame($site->id, $workRequest->site_id);
        $this->assertSame($department->id, $workRequest->department_id);
        $this->assertSame($capability->id, $workRequest->required_capability_id);
        $this->assertSame($sop->id, $workRequest->standard_operating_procedure_id);
        $this->assertSame($definition->id, $workRequest->business_process_definition_id);
        $this->assertSame($run->id, $workRequest->business_process_run_id);
        $this->assertSame($runStep->id, $workRequest->business_process_run_step_id);
        $this->assertSame($template->id, $workRequest->assignment_template_id);
        $this->assertNull($workRequest->assignment_id);

        $this->assertSame('Prepare research package', $workRequest->title);
        $this->assertSame('business_process_step', $workRequest->assignment_type);
        $this->assertSame('high', $workRequest->priority);
        $this->assertSame('pending', $workRequest->status);
        $this->assertSame('first_available', $workRequest->routing_strategy);
        $this->assertSame('business_process_run_step', $workRequest->source_type);
        $this->assertSame($runStep->id, $workRequest->source_id);
        $this->assertSame(['summary' => 'Prepare research from the brief.'], $workRequest->briefing);
        $this->assertSame('Research package with reliable sources.', $workRequest->expected_output);
        $this->assertSame(['brief' => 'July package'], $workRequest->input_payload);
        $this->assertTrue($workRequest->review_required);
        $this->assertSame('Human supervisor approval', $workRequest->review_path);
        $this->assertSame('2026-07-01 13:30:00', $workRequest->due_at->format('Y-m-d H:i:s'));
        $this->assertNotNull($workRequest->work_request_key);
        $this->assertNotNull($workRequest->requested_at);

        $this->assertDatabaseHas('process_events', [
            'organization_id' => $organization->id,
            'business_process_definition_id' => $definition->id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $runStep->id,
            'event_type' => 'work_request_created',
        ]);
        $this->assertDatabaseHas('process_logs', [
            'organization_id' => $organization->id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $runStep->id,
            'log_level' => 'info',
        ]);

        $activity = Activity::query()
            ->where('organization_id', $organization->id)
            ->where('activity_type', 'work_request_created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($site->id, $activity->site_id);
        $this->assertSame($department->id, $activity->department_id);
        $this->assertSame('Work Request created: Prepare research package', $activity->title);
        $this->assertSame($workRequest->id, $activity->metadata['work_request_id']);

        $auditLog = AuditLog::query()
            ->where('organization_id', $organization->id)
            ->where('auditable_type', WorkRequest::class)
            ->where('auditable_id', $workRequest->id)
            ->where('event_type', 'work_request_created')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('created', $auditLog->action);
        $this->assertSame('pending', $auditLog->after_state['status']);

        $this->assertSame(1, ProcessEvent::query()->where('event_type', 'work_request_created')->count());
        $this->assertGreaterThanOrEqual(
            1,
            ProcessLog::query()
                ->where('business_process_run_id', $run->id)
                ->where('business_process_run_step_id', $runStep->id)
                ->whereNotNull('process_event_id')
                ->count(),
        );
        $this->assertSame(0, Assignment::query()->count());
    }

    public function test_non_ready_run_step_is_rejected(): void
    {
        ['definition' => $definition, 'site' => $site] = $this->businessProcessFixture(
            dependencyRules: [
                [
                    'step_key' => 'missing-prerequisite',
                    'required_status' => 'completed',
                ],
            ],
        );

        $run = app(ProcessRunService::class)->start(
            definition: $definition,
            site: $site,
            title: 'Blocked by dependency',
            inputPayload: ['brief' => 'July package'],
        );
        $runStep = $run->runSteps()->where('status', 'waiting_for_dependency')->firstOrFail();

        try {
            app(WorkRequestCreationService::class)->createFromRunStep($runStep);
            $this->fail('Expected non-ready Run Step to be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Only ready Run Steps can create Work Requests.', $exception->getMessage());
        }

        $this->assertDatabaseCount('work_requests', 0);
        $this->assertSame(0, Assignment::query()->count());
    }

    /**
     * @param  array<int, array<string, string>>  $dependencyRules
     * @return array<string, mixed>
     */
    private function businessProcessFixture(array $dependencyRules = []): array
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
            ->create([
                'status' => 'active',
                'name' => 'Prepare Editorial Package',
                'process_key' => 'prepare-editorial-package',
            ]);
        $step = BusinessProcessStep::factory()
            ->for($organization)
            ->for($definition)
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'step_key' => 'research',
                'name' => 'Research',
                'status' => 'active',
                'sort_order' => 1,
                'dependency_rules' => $dependencyRules,
                'approval_required' => true,
                'expected_output' => 'Research package with reliable sources.',
            ]);
        $template = AssignmentTemplate::factory()
            ->for($organization)
            ->for($definition)
            ->for($step, 'businessProcessStep')
            ->for($department)
            ->for($sop, 'standardOperatingProcedure')
            ->for($capability, 'requiredCapability')
            ->create([
                'template_key' => 'research',
                'title_template' => 'Prepare research package',
                'assignment_type' => 'business_process_step',
                'priority' => 'high',
                'briefing_template' => ['summary' => 'Prepare research from the brief.'],
                'expected_output' => 'Research package with reliable sources.',
                'input_mapping' => ['source' => 'process_run_step'],
                'review_required' => true,
                'review_path' => 'Human supervisor approval',
                'due_offset_minutes' => 90,
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
        ];
    }
}
