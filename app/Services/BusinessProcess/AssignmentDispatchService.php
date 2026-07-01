<?php

namespace App\Services\BusinessProcess;

use App\Models\Assignment;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\WorkRequest;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignmentDispatchService
{
    public function dispatch(RoutingDecision $routingDecision): Assignment
    {
        $routingDecision->loadMissing([
            'selectedEmployee',
            'workRequest.requiredCapability',
            'workRequest.businessProcessRunStep',
        ]);

        $workRequest = $routingDecision->workRequest;

        if ($routingDecision->status !== 'selected') {
            throw new DomainException('Only selected Routing Decisions can dispatch Assignments.');
        }

        if ($routingDecision->selected_employee_id === null) {
            throw new DomainException('Selected Routing Decisions require a selected Employee before dispatch.');
        }

        if ($workRequest->assignment_id !== null || Assignment::query()->where('work_request_id', $workRequest->id)->exists()) {
            throw new DomainException('Work Request has already been dispatched.');
        }

        if ($workRequest->status !== 'routed') {
            throw new DomainException('Only routed Work Requests can be dispatched.');
        }

        return DB::transaction(function () use ($routingDecision, $workRequest): Assignment {
            $assignment = Assignment::query()->create([
                'organization_id' => $workRequest->organization_id,
                'site_id' => $workRequest->site_id,
                'department_id' => $workRequest->department_id,
                'employee_id' => $routingDecision->selected_employee_id,
                'standard_operating_procedure_id' => $workRequest->standard_operating_procedure_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'work_request_id' => $workRequest->id,
                'routing_decision_id' => $routingDecision->id,
                'title' => $workRequest->title,
                'assignment_type' => $workRequest->assignment_type,
                'priority' => $workRequest->priority,
                'status' => 'pending',
                'briefing' => $workRequest->briefing,
                'expected_output' => $workRequest->expected_output,
                'input_payload' => $workRequest->input_payload,
                'output_payload' => null,
                'required_capability_keys' => $this->requiredCapabilityKeys($workRequest),
                'confidence_score' => null,
                'quality_score' => null,
                'escalation_required' => false,
                'review_required' => $workRequest->review_required,
                'review_path' => $workRequest->review_path,
                'due_at' => $workRequest->due_at,
                'started_at' => null,
                'completed_at' => null,
            ]);

            $workRequest->update([
                'status' => 'assignment_created',
                'assignment_id' => $assignment->id,
                'dispatched_at' => now(),
            ]);

            $routingDecision->update([
                'assignment_id' => $assignment->id,
            ]);

            $runStep = $workRequest->businessProcessRunStep;

            if ($runStep !== null) {
                $runStep->update([
                    'status' => 'assignment_created',
                    'assignment_id' => $assignment->id,
                ]);
            }

            $this->recordProcessSideEffects($routingDecision->refresh(), $workRequest->refresh(), $assignment);

            return $assignment->refresh();
        });
    }

    /**
     * @return array<int, string>
     */
    private function requiredCapabilityKeys(WorkRequest $workRequest): array
    {
        if ($workRequest->requiredCapability === null) {
            return [];
        }

        return [$workRequest->requiredCapability->capability_key];
    }

    private function recordProcessSideEffects(RoutingDecision $routingDecision, WorkRequest $workRequest, Assignment $assignment): void
    {
        $event = ProcessEvent::query()->create([
            'organization_id' => $workRequest->organization_id,
            'business_process_definition_id' => $workRequest->business_process_definition_id,
            'business_process_run_id' => $workRequest->business_process_run_id,
            'business_process_run_step_id' => $workRequest->business_process_run_step_id,
            'assignment_id' => $assignment->id,
            'actor_type' => null,
            'actor_id' => null,
            'event_type' => 'assignment_created',
            'event_key' => Str::uuid()->toString(),
            'summary' => "Assignment created: {$assignment->title}",
            'payload' => [
                'work_request_id' => $workRequest->id,
                'routing_decision_id' => $routingDecision->id,
                'assignment_id' => $assignment->id,
                'selected_employee_id' => $routingDecision->selected_employee_id,
                'status' => $assignment->status,
            ],
            'occurred_at' => now(),
            'created_at' => now(),
        ]);

        if ($workRequest->business_process_run_id === null) {
            return;
        }

        ProcessLog::query()->create([
            'organization_id' => $workRequest->organization_id,
            'business_process_run_id' => $workRequest->business_process_run_id,
            'business_process_run_step_id' => $workRequest->business_process_run_step_id,
            'process_event_id' => $event->id,
            'assignment_id' => $assignment->id,
            'log_level' => 'info',
            'message' => "Assignment {$assignment->id} created from Work Request {$workRequest->work_request_key}.",
            'context' => [
                'work_request_id' => $workRequest->id,
                'routing_decision_id' => $routingDecision->id,
                'assignment_id' => $assignment->id,
                'selected_employee_id' => $routingDecision->selected_employee_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
            ],
            'created_at' => now(),
        ]);
    }
}
