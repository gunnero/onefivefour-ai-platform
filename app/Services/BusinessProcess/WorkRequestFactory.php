<?php

namespace App\Services\BusinessProcess;

use App\Models\AssignmentTemplate;
use App\Models\BusinessProcessRunStep;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\WorkRequest;
use App\Services\ActivityService;
use App\Services\AuditLogService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkRequestFactory
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function createFromRunStep(BusinessProcessRunStep $runStep): WorkRequest
    {
        $runStep->loadMissing([
            'businessProcessRun.businessProcessDefinition',
            'businessProcessRun.site',
            'businessProcessStep.assignmentTemplates',
            'department',
            'standardOperatingProcedure',
            'requiredCapability',
        ]);

        if ($runStep->status !== 'ready') {
            throw new DomainException('Only ready Run Steps can create Work Requests.');
        }

        $template = $this->assignmentTemplateFor($runStep);

        return DB::transaction(function () use ($runStep, $template): WorkRequest {
            $run = $runStep->businessProcessRun;
            $definition = $run->businessProcessDefinition;
            $dueAt = $template->due_offset_minutes === null
                ? null
                : now()->addMinutes($template->due_offset_minutes);

            $workRequest = WorkRequest::query()->create([
                'organization_id' => $runStep->organization_id,
                'site_id' => $run->site_id,
                'department_id' => $runStep->department_id,
                'business_process_definition_id' => $definition->id,
                'business_process_run_id' => $run->id,
                'business_process_run_step_id' => $runStep->id,
                'assignment_template_id' => $template->id,
                'standard_operating_procedure_id' => $runStep->standard_operating_procedure_id,
                'required_capability_id' => $runStep->required_capability_id,
                'requested_by_user_id' => null,
                'source_type' => 'business_process_run_step',
                'source_id' => $runStep->id,
                'work_request_key' => Str::uuid()->toString(),
                'title' => $template->title_template,
                'assignment_type' => $template->assignment_type,
                'priority' => $template->priority,
                'status' => 'pending',
                'routing_strategy' => ($template->metadata ?? [])['routing_strategy'] ?? 'first_available',
                'briefing' => $template->briefing_template ?? [],
                'expected_output' => $template->expected_output,
                'input_payload' => $runStep->input_payload ?? $run->input_payload,
                'review_required' => $template->review_required,
                'review_path' => $template->review_path,
                'due_at' => $dueAt,
                'assignment_id' => null,
                'blocked_reason' => null,
                'failure_reason' => null,
                'escalation_reason' => null,
                'requested_at' => now(),
                'routing_started_at' => null,
                'routed_at' => null,
                'dispatched_at' => null,
                'blocked_at' => null,
                'failed_at' => null,
                'cancelled_at' => null,
                'metadata' => [
                    'created_by' => self::class,
                ],
            ]);

            $event = ProcessEvent::query()->create([
                'organization_id' => $workRequest->organization_id,
                'business_process_definition_id' => $workRequest->business_process_definition_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'assignment_id' => null,
                'actor_type' => null,
                'actor_id' => null,
                'event_type' => 'work_request_created',
                'event_key' => Str::uuid()->toString(),
                'summary' => "Work Request created: {$workRequest->title}",
                'payload' => [
                    'assignment_template_id' => $template->id,
                    'business_process_run_step_id' => $runStep->id,
                    'work_request_id' => $workRequest->id,
                    'status' => $workRequest->status,
                ],
                'occurred_at' => now(),
                'created_at' => now(),
            ]);

            ProcessLog::query()->create([
                'organization_id' => $workRequest->organization_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'process_event_id' => $event->id,
                'assignment_id' => null,
                'log_level' => 'info',
                'message' => "Work Request {$workRequest->work_request_key} created from ready Run Step.",
                'context' => [
                    'assignment_template_id' => $template->id,
                    'business_process_run_step_id' => $runStep->id,
                    'work_request_id' => $workRequest->id,
                    'routing_strategy' => $workRequest->routing_strategy,
                ],
                'created_at' => now(),
            ]);

            $auditLog = $this->auditLogService->record(
                auditable: $workRequest,
                eventType: 'work_request_created',
                action: 'created',
                summary: "Work Request created: {$workRequest->title}",
                afterState: [
                    'id' => $workRequest->id,
                    'business_process_run_id' => $workRequest->business_process_run_id,
                    'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                    'assignment_template_id' => $workRequest->assignment_template_id,
                    'status' => $workRequest->status,
                    'routing_strategy' => $workRequest->routing_strategy,
                ],
            );

            $this->activityService->workRequestCreated($workRequest, $auditLog);

            return $workRequest->refresh();
        });
    }

    private function assignmentTemplateFor(BusinessProcessRunStep $runStep): AssignmentTemplate
    {
        $template = $runStep->businessProcessStep
            ->assignmentTemplates()
            ->where('organization_id', $runStep->organization_id)
            ->orderBy('id')
            ->first();

        if ($template === null) {
            throw new DomainException('Ready Run Steps require an Assignment Template before creating Work Requests.');
        }

        return $template;
    }
}
