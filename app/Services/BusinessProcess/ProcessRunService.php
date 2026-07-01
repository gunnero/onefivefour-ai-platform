<?php

namespace App\Services\BusinessProcess;

use App\Models\Assignment;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\BusinessProcessStep;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\User;
use App\Models\WorkRequest;
use App\Services\ActivityService;
use App\Services\AuditLogService;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessRunService
{
    private const CANCELLABLE_ASSIGNMENT_STATUSES = [
        'pending',
        'accepted',
        'in_progress',
        'blocked',
        'needs_review',
    ];

    private const CANCELLABLE_RUN_STEP_STATUSES = [
        'pending',
        'ready',
        'waiting_for_dependency',
        'waiting_for_approval',
        'blocked',
        'assignment_created',
        'in_progress',
    ];

    private const CANCELLABLE_WORK_REQUEST_STATUSES = [
        'pending',
        'routing',
        'waiting_for_manual_selection',
        'routed',
        'blocked',
        'escalated',
    ];

    private const CANCELLABLE_ROUTING_DECISION_STATUSES = [
        'pending',
        'evaluating',
        'selected',
        'manual_required',
    ];

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    /**
     * @param  array<string, mixed>  $inputPayload
     */
    public function start(
        BusinessProcessDefinition $definition,
        ?Site $site = null,
        ?User $startedBy = null,
        ?string $title = null,
        array $inputPayload = [],
        string $priority = 'normal',
    ): BusinessProcessRun {
        if ($definition->status !== 'active') {
            throw new DomainException('Only active Business Process Definitions can start Process Runs.');
        }

        if ($site !== null && $site->organization_id !== $definition->organization_id) {
            throw new DomainException('Process Run Site must belong to the same Organization as the Process Definition.');
        }

        /** @var Collection<int, BusinessProcessStep> $steps */
        $steps = $definition->steps()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($steps->isEmpty()) {
            throw new DomainException('Active Business Process Definitions require at least one active Process Step.');
        }

        return DB::transaction(function () use ($definition, $site, $startedBy, $title, $inputPayload, $priority, $steps): BusinessProcessRun {
            $run = BusinessProcessRun::query()->create([
                'organization_id' => $definition->organization_id,
                'business_process_definition_id' => $definition->id,
                'site_id' => $site?->id,
                'started_by_user_id' => $startedBy?->id,
                'current_run_step_id' => null,
                'run_key' => Str::uuid()->toString(),
                'title' => $title ?? "{$definition->name} Run",
                'status' => 'running',
                'priority' => $priority,
                'input_payload' => $inputPayload,
                'output_payload' => null,
                'progress_percent' => 0,
                'started_at' => now(),
                'completed_at' => null,
                'cancelled_at' => null,
                'failed_at' => null,
                'blocked_at' => null,
                'metadata' => [],
            ]);

            $runStartedEvent = $this->recordProcessEvent(
                definition: $definition,
                run: $run,
                eventType: 'process_run_started',
                summary: "Process Run started: {$run->title}",
                payload: [
                    'business_process_definition_id' => $definition->id,
                    'business_process_definition_version' => $definition->version,
                    'input_payload' => $inputPayload,
                    'priority' => $priority,
                ],
                actor: $startedBy,
            );

            $this->recordProcessLog(
                run: $run,
                event: $runStartedEvent,
                message: "Process Run {$run->run_key} created from {$definition->process_key} v{$definition->version}.",
                context: [
                    'business_process_definition_id' => $definition->id,
                    'active_step_count' => $steps->count(),
                ],
            );

            $firstReadyRunStep = null;

            foreach ($steps as $step) {
                $isFirstDependencyFreeStep = $firstReadyRunStep === null && $this->isDependencyFree($step);
                $status = $isFirstDependencyFreeStep ? 'ready' : 'waiting_for_dependency';

                $runStep = BusinessProcessRunStep::query()->create([
                    'organization_id' => $definition->organization_id,
                    'business_process_run_id' => $run->id,
                    'business_process_step_id' => $step->id,
                    'assignment_id' => null,
                    'department_id' => $step->department_id,
                    'employee_id' => null,
                    'standard_operating_procedure_id' => $step->standard_operating_procedure_id,
                    'required_capability_id' => $step->required_capability_id,
                    'status' => $status,
                    'sort_order' => $step->sort_order,
                    'attempt_number' => 1,
                    'approval_required' => $step->approval_required,
                    'approval_status' => null,
                    'blocked_reason' => null,
                    'failure_reason' => null,
                    'input_payload' => $inputPayload,
                    'output_payload' => null,
                    'ready_at' => $isFirstDependencyFreeStep ? now() : null,
                    'started_at' => null,
                    'completed_at' => null,
                    'blocked_at' => null,
                    'failed_at' => null,
                    'cancelled_at' => null,
                ]);

                $this->recordProcessLog(
                    run: $run,
                    runStep: $runStep,
                    message: "Run Step dependency status evaluated: {$step->name}.",
                    context: [
                        'business_process_step_id' => $step->id,
                        'step_key' => $step->step_key,
                        'dependency_rules' => $step->dependency_rules ?? [],
                        'status' => $status,
                    ],
                );

                if ($isFirstDependencyFreeStep) {
                    $firstReadyRunStep = $runStep;
                }
            }

            if ($firstReadyRunStep !== null) {
                $run->update(['current_run_step_id' => $firstReadyRunStep->id]);

                $readyEvent = $this->recordProcessEvent(
                    definition: $definition,
                    run: $run,
                    runStep: $firstReadyRunStep,
                    eventType: 'run_step_ready',
                    summary: "Run Step ready: {$firstReadyRunStep->businessProcessStep?->name}",
                    payload: [
                        'business_process_step_id' => $firstReadyRunStep->business_process_step_id,
                        'status' => 'ready',
                    ],
                    actor: $startedBy,
                );

                $this->recordProcessLog(
                    run: $run,
                    runStep: $firstReadyRunStep,
                    event: $readyEvent,
                    message: 'First dependency-free Run Step marked ready.',
                    context: [
                        'business_process_run_step_id' => $firstReadyRunStep->id,
                        'business_process_step_id' => $firstReadyRunStep->business_process_step_id,
                    ],
                );
            }

            $run->refresh();

            $auditLog = $this->auditLogService->record(
                auditable: $run,
                eventType: 'process_run_started',
                action: 'started',
                summary: "Process Run started: {$run->title}",
                afterState: [
                    'id' => $run->id,
                    'business_process_definition_id' => $run->business_process_definition_id,
                    'site_id' => $run->site_id,
                    'title' => $run->title,
                    'status' => $run->status,
                    'priority' => $run->priority,
                    'current_run_step_id' => $run->current_run_step_id,
                    'started_at' => $run->started_at?->toISOString(),
                ],
                actor: $startedBy,
            );

            $this->activityService->processRunStarted($run, $auditLog);

            return $run;
        });
    }

    public function advanceFromCompletedAssignment(Assignment $assignment): ?BusinessProcessRun
    {
        if ($assignment->business_process_run_id === null || $assignment->business_process_run_step_id === null) {
            return null;
        }

        return DB::transaction(function () use ($assignment): ?BusinessProcessRun {
            $runStep = BusinessProcessRunStep::query()
                ->whereKey($assignment->business_process_run_step_id)
                ->lockForUpdate()
                ->with(['businessProcessStep', 'department', 'assignment'])
                ->first();

            if ($runStep === null) {
                return null;
            }

            $run = BusinessProcessRun::query()
                ->whereKey($runStep->business_process_run_id)
                ->lockForUpdate()
                ->with(['businessProcessDefinition', 'site'])
                ->first();

            if ($run === null) {
                return null;
            }

            if (! in_array($runStep->status, ['completed', 'waiting_for_approval'], true)) {
                $this->completeRunStepFromAssignment($run, $runStep, $assignment);
            }

            $readyRunSteps = collect();

            if ($runStep->refresh()->status === 'completed') {
                $readyRunSteps = $this->markReadyDependentRunSteps($run, $assignment);
            }

            $runSteps = $this->runStepsForEvaluation($run);

            if ($this->allRunStepsCompleted($runSteps)) {
                $this->completeProcessRun($run, $assignment);

                return $run->refresh();
            }

            $this->updateRunningProcessRun($run, $runSteps, $readyRunSteps);

            return $run->refresh();
        });
    }

    public function blockFromAssignment(Assignment $assignment, ?string $reason = null): ?BusinessProcessRun
    {
        return $this->stopFromAssignment(
            assignment: $assignment,
            runStepStatus: 'blocked',
            runStatus: 'blocked',
            reason: $reason,
        );
    }

    public function failFromAssignment(Assignment $assignment, ?string $reason = null): ?BusinessProcessRun
    {
        return $this->stopFromAssignment(
            assignment: $assignment,
            runStepStatus: 'failed',
            runStatus: 'failed',
            reason: $reason,
        );
    }

    public function cancel(BusinessProcessRun $run, ?string $reason = null): BusinessProcessRun
    {
        return DB::transaction(function () use ($run, $reason): BusinessProcessRun {
            $run = BusinessProcessRun::query()
                ->whereKey($run->id)
                ->lockForUpdate()
                ->with(['businessProcessDefinition', 'site'])
                ->firstOrFail();

            $cancelledRunStepIds = $this->cancelRunSteps($run, $reason);
            $cancelledWorkRequestIds = $this->cancelWorkRequests($run, $reason);
            $cancelledRoutingDecisionIds = $this->cancelRoutingDecisions($run, $reason);
            $cancelledAssignmentIds = $this->cancelAssignments($run);
            $metadata = $run->metadata ?? [];

            if ($reason !== null) {
                $metadata['cancellation_reason'] = $reason;
            }

            $run->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => $metadata,
            ]);

            $run->refresh();

            $event = $this->recordProcessEvent(
                definition: $run->businessProcessDefinition,
                run: $run,
                eventType: 'process_run_cancelled',
                summary: "Process Run cancelled: {$run->title}",
                payload: [
                    'business_process_run_id' => $run->id,
                    'business_process_definition_id' => $run->business_process_definition_id,
                    'cancelled_run_step_ids' => $cancelledRunStepIds,
                    'cancelled_work_request_ids' => $cancelledWorkRequestIds,
                    'cancelled_routing_decision_ids' => $cancelledRoutingDecisionIds,
                    'cancelled_assignment_ids' => $cancelledAssignmentIds,
                    'reason' => $reason,
                    'status' => $run->status,
                ],
            );

            $this->recordProcessLog(
                run: $run,
                event: $event,
                message: "Process Run {$run->run_key} cancelled.",
                context: [
                    'business_process_run_id' => $run->id,
                    'cancelled_run_step_ids' => $cancelledRunStepIds,
                    'cancelled_work_request_ids' => $cancelledWorkRequestIds,
                    'cancelled_routing_decision_ids' => $cancelledRoutingDecisionIds,
                    'cancelled_assignment_ids' => $cancelledAssignmentIds,
                    'reason' => $reason,
                    'status' => $run->status,
                ],
            );

            $auditLog = $this->auditLogService->record(
                auditable: $run,
                eventType: 'process_run_cancelled',
                action: 'cancelled',
                summary: "Process Run cancelled: {$run->title}",
                afterState: [
                    'id' => $run->id,
                    'business_process_definition_id' => $run->business_process_definition_id,
                    'status' => $run->status,
                    'cancelled_at' => $run->cancelled_at?->toISOString(),
                    'cancellation_reason' => $reason,
                    'cancelled_run_step_ids' => $cancelledRunStepIds,
                    'cancelled_work_request_ids' => $cancelledWorkRequestIds,
                    'cancelled_routing_decision_ids' => $cancelledRoutingDecisionIds,
                    'cancelled_assignment_ids' => $cancelledAssignmentIds,
                ],
            );

            $this->activityService->processRunCancelled($run, $auditLog);

            return $run->refresh();
        });
    }

    private function isDependencyFree(BusinessProcessStep $step): bool
    {
        return ($step->dependency_rules ?? []) === [];
    }

    private function stopFromAssignment(
        Assignment $assignment,
        string $runStepStatus,
        string $runStatus,
        ?string $reason,
    ): ?BusinessProcessRun {
        if ($assignment->business_process_run_id === null || $assignment->business_process_run_step_id === null) {
            return null;
        }

        return DB::transaction(function () use ($assignment, $runStepStatus, $runStatus, $reason): ?BusinessProcessRun {
            $runStep = BusinessProcessRunStep::query()
                ->whereKey($assignment->business_process_run_step_id)
                ->lockForUpdate()
                ->with(['businessProcessStep', 'department', 'assignment'])
                ->first();

            if ($runStep === null) {
                return null;
            }

            $run = BusinessProcessRun::query()
                ->whereKey($runStep->business_process_run_id)
                ->lockForUpdate()
                ->with(['businessProcessDefinition', 'site'])
                ->first();

            if ($run === null) {
                return null;
            }

            $runStepAttributes = [
                'status' => $runStepStatus,
            ];

            if ($runStepStatus === 'blocked') {
                $runStepAttributes['blocked_at'] = now();
                $runStepAttributes['blocked_reason'] = $reason;
            } else {
                $runStepAttributes['failed_at'] = now();
                $runStepAttributes['failure_reason'] = $reason;
            }

            $runStep->update($runStepAttributes);
            $runStep->refresh();

            $runMetadata = $run->metadata ?? [];

            if ($reason !== null) {
                $runMetadata[$runStatus === 'failed' ? 'failure_reason' : 'blocked_reason'] = $reason;
            }

            $runAttributes = [
                'status' => $runStatus,
                'metadata' => $runMetadata,
            ];

            if ($runStatus === 'blocked') {
                $runAttributes['blocked_at'] = now();
            } else {
                $runAttributes['failed_at'] = now();
            }

            $run->update($runAttributes);
            $run->refresh();

            $runStepEventType = "run_step_{$runStepStatus}";
            $runEventType = "process_run_{$runStatus}";

            $this->recordStoppedRunStepSideEffects($run, $runStep, $assignment, $runStepEventType, $reason);
            $this->recordStoppedProcessRunSideEffects($run, $assignment, $runEventType, $reason);

            return $run->refresh();
        });
    }

    private function recordStoppedRunStepSideEffects(
        BusinessProcessRun $run,
        BusinessProcessRunStep $runStep,
        Assignment $assignment,
        string $eventType,
        ?string $reason,
    ): void {
        $event = $this->recordProcessEvent(
            definition: $run->businessProcessDefinition,
            run: $run,
            runStep: $runStep,
            assignment: $assignment,
            eventType: $eventType,
            summary: "Run Step {$runStep->status}: {$runStep->businessProcessStep?->name}",
            payload: [
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $assignment->id,
                'reason' => $reason,
                'status' => $runStep->status,
            ],
        );

        $this->recordProcessLog(
            run: $run,
            runStep: $runStep,
            event: $event,
            assignment: $assignment,
            message: "Run Step {$runStep->id} became {$runStep->status} from Assignment {$assignment->id}.",
            context: [
                'business_process_run_step_id' => $runStep->id,
                'assignment_id' => $assignment->id,
                'reason' => $reason,
                'status' => $runStep->status,
            ],
        );

        $auditLog = $this->auditLogService->record(
            auditable: $runStep,
            eventType: $eventType,
            action: $runStep->status,
            summary: "Run Step {$runStep->status}: {$runStep->businessProcessStep?->name}",
            afterState: [
                'id' => $runStep->id,
                'business_process_run_id' => $runStep->business_process_run_id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $runStep->assignment_id,
                'status' => $runStep->status,
                'blocked_reason' => $runStep->blocked_reason,
                'failure_reason' => $runStep->failure_reason,
                'blocked_at' => $runStep->blocked_at?->toISOString(),
                'failed_at' => $runStep->failed_at?->toISOString(),
            ],
        );

        if ($eventType === 'run_step_blocked') {
            $this->activityService->runStepBlocked($runStep, $auditLog);

            return;
        }

        $this->activityService->runStepFailed($runStep, $auditLog);
    }

    private function recordStoppedProcessRunSideEffects(
        BusinessProcessRun $run,
        Assignment $assignment,
        string $eventType,
        ?string $reason,
    ): void {
        $event = $this->recordProcessEvent(
            definition: $run->businessProcessDefinition,
            run: $run,
            assignment: $assignment,
            eventType: $eventType,
            summary: "Process Run {$run->status}: {$run->title}",
            payload: [
                'business_process_run_id' => $run->id,
                'business_process_definition_id' => $run->business_process_definition_id,
                'assignment_id' => $assignment->id,
                'reason' => $reason,
                'status' => $run->status,
            ],
        );

        $this->recordProcessLog(
            run: $run,
            event: $event,
            assignment: $assignment,
            message: "Process Run {$run->run_key} became {$run->status}.",
            context: [
                'business_process_run_id' => $run->id,
                'assignment_id' => $assignment->id,
                'reason' => $reason,
                'status' => $run->status,
            ],
        );

        $auditLog = $this->auditLogService->record(
            auditable: $run,
            eventType: $eventType,
            action: $run->status,
            summary: "Process Run {$run->status}: {$run->title}",
            afterState: [
                'id' => $run->id,
                'business_process_definition_id' => $run->business_process_definition_id,
                'status' => $run->status,
                'blocked_at' => $run->blocked_at?->toISOString(),
                'failed_at' => $run->failed_at?->toISOString(),
                'blocked_reason' => ($run->metadata ?? [])['blocked_reason'] ?? null,
                'failure_reason' => ($run->metadata ?? [])['failure_reason'] ?? null,
            ],
        );

        if ($eventType === 'process_run_blocked') {
            $this->activityService->processRunBlocked($run, $auditLog);

            return;
        }

        $this->activityService->processRunFailed($run, $auditLog);
    }

    /**
     * @return array<int, int>
     */
    private function cancelRunSteps(BusinessProcessRun $run, ?string $reason): array
    {
        $cancelledRunStepIds = [];

        $run->runSteps()
            ->with(['businessProcessStep', 'department', 'assignment'])
            ->whereIn('status', self::CANCELLABLE_RUN_STEP_STATUSES)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(function (BusinessProcessRunStep $runStep) use ($run, $reason, &$cancelledRunStepIds): void {
                $runStep->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
                $runStep->refresh();
                $cancelledRunStepIds[] = $runStep->id;

                $event = $this->recordProcessEvent(
                    definition: $run->businessProcessDefinition,
                    run: $run,
                    runStep: $runStep,
                    eventType: 'run_step_cancelled',
                    summary: "Run Step cancelled: {$runStep->businessProcessStep?->name}",
                    payload: [
                        'business_process_run_step_id' => $runStep->id,
                        'business_process_step_id' => $runStep->business_process_step_id,
                        'reason' => $reason,
                        'status' => $runStep->status,
                    ],
                );

                $this->recordProcessLog(
                    run: $run,
                    runStep: $runStep,
                    event: $event,
                    message: "Run Step {$runStep->id} cancelled during Process Run cancellation.",
                    context: [
                        'business_process_run_step_id' => $runStep->id,
                        'reason' => $reason,
                        'status' => $runStep->status,
                    ],
                );

                $auditLog = $this->auditLogService->record(
                    auditable: $runStep,
                    eventType: 'run_step_cancelled',
                    action: 'cancelled',
                    summary: "Run Step cancelled: {$runStep->businessProcessStep?->name}",
                    afterState: [
                        'id' => $runStep->id,
                        'business_process_run_id' => $runStep->business_process_run_id,
                        'business_process_step_id' => $runStep->business_process_step_id,
                        'status' => $runStep->status,
                        'cancelled_at' => $runStep->cancelled_at?->toISOString(),
                        'cancellation_reason' => $reason,
                    ],
                );

                $this->activityService->runStepCancelled($runStep, $auditLog, $reason);
            });

        return $cancelledRunStepIds;
    }

    /**
     * @return array<int, int>
     */
    private function cancelWorkRequests(BusinessProcessRun $run, ?string $reason): array
    {
        $cancelledWorkRequestIds = [];

        $run->workRequests()
            ->with(['organization', 'site', 'department'])
            ->whereIn('status', self::CANCELLABLE_WORK_REQUEST_STATUSES)
            ->whereNull('assignment_id')
            ->orderBy('id')
            ->get()
            ->each(function (WorkRequest $workRequest) use ($run, $reason, &$cancelledWorkRequestIds): void {
                $metadata = $workRequest->metadata ?? [];

                if ($reason !== null) {
                    $metadata['cancellation_reason'] = $reason;
                }

                $workRequest->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'metadata' => $metadata,
                ]);
                $workRequest->refresh();
                $cancelledWorkRequestIds[] = $workRequest->id;

                $event = $this->recordProcessEvent(
                    definition: $run->businessProcessDefinition,
                    run: $run,
                    runStep: $workRequest->businessProcessRunStep,
                    eventType: 'work_request_cancelled',
                    summary: "Work Request cancelled: {$workRequest->title}",
                    payload: [
                        'work_request_id' => $workRequest->id,
                        'reason' => $reason,
                        'status' => $workRequest->status,
                    ],
                );

                $this->recordProcessLog(
                    run: $run,
                    runStep: $workRequest->businessProcessRunStep,
                    event: $event,
                    message: "Work Request {$workRequest->work_request_key} cancelled during Process Run cancellation.",
                    context: [
                        'work_request_id' => $workRequest->id,
                        'reason' => $reason,
                        'status' => $workRequest->status,
                    ],
                );

                $auditLog = $this->auditLogService->record(
                    auditable: $workRequest,
                    eventType: 'work_request_cancelled',
                    action: 'cancelled',
                    summary: "Work Request cancelled: {$workRequest->title}",
                    afterState: [
                        'id' => $workRequest->id,
                        'business_process_run_id' => $workRequest->business_process_run_id,
                        'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                        'status' => $workRequest->status,
                        'cancelled_at' => $workRequest->cancelled_at?->toISOString(),
                        'cancellation_reason' => $reason,
                    ],
                );

                $this->activityService->workRequestCancelled($workRequest, $auditLog);
            });

        return $cancelledWorkRequestIds;
    }

    /**
     * @return array<int, int>
     */
    private function cancelRoutingDecisions(BusinessProcessRun $run, ?string $reason): array
    {
        $cancelledRoutingDecisionIds = [];

        $routingDecisions = RoutingDecision::query()
            ->whereIn('status', self::CANCELLABLE_ROUTING_DECISION_STATUSES)
            ->whereHas('workRequest', function ($query) use ($run): void {
                $query->where('business_process_run_id', $run->id);
            })
            ->orderBy('id')
            ->get();

        foreach ($routingDecisions as $routingDecision) {
            $routingDecision->update([
                'status' => 'cancelled',
                'failure_reason' => $reason,
            ]);
            $cancelledRoutingDecisionIds[] = $routingDecision->id;

            $this->auditLogService->record(
                auditable: $routingDecision,
                eventType: 'routing_decision_cancelled',
                action: 'cancelled',
                summary: "Routing Decision cancelled for Work Request {$routingDecision->work_request_id}.",
                afterState: [
                    'id' => $routingDecision->id,
                    'work_request_id' => $routingDecision->work_request_id,
                    'status' => $routingDecision->status,
                    'failure_reason' => $routingDecision->failure_reason,
                ],
            );
        }

        return $cancelledRoutingDecisionIds;
    }

    /**
     * @return array<int, int>
     */
    private function cancelAssignments(BusinessProcessRun $run): array
    {
        $cancelledAssignmentIds = [];

        $run->assignments()
            ->whereIn('status', self::CANCELLABLE_ASSIGNMENT_STATUSES)
            ->orderBy('id')
            ->get()
            ->each(function (Assignment $assignment) use (&$cancelledAssignmentIds): void {
                $assignment->update(['status' => 'cancelled']);
                $cancelledAssignmentIds[] = $assignment->id;
            });

        return $cancelledAssignmentIds;
    }

    private function completeRunStepFromAssignment(BusinessProcessRun $run, BusinessProcessRunStep $runStep, Assignment $assignment): void
    {
        $status = $runStep->approval_required ? 'waiting_for_approval' : 'completed';

        $runStep->update([
            'status' => $status,
            'approval_status' => $runStep->approval_required ? 'pending' : $runStep->approval_status,
            'output_payload' => $assignment->output_payload,
            'completed_at' => $assignment->completed_at ?? now(),
        ]);

        $runStep->refresh();

        $event = $this->recordProcessEvent(
            definition: $run->businessProcessDefinition,
            run: $run,
            runStep: $runStep,
            assignment: $assignment,
            eventType: 'run_step_completed',
            summary: "Run Step completed: {$runStep->businessProcessStep?->name}",
            payload: [
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $assignment->id,
                'status' => $runStep->status,
                'approval_required' => $runStep->approval_required,
            ],
        );

        $this->recordProcessLog(
            run: $run,
            runStep: $runStep,
            event: $event,
            assignment: $assignment,
            message: "Run Step {$runStep->id} completed from Assignment {$assignment->id}.",
            context: [
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $assignment->id,
                'status' => $runStep->status,
            ],
        );

        $auditLog = $this->auditLogService->record(
            auditable: $runStep,
            eventType: 'run_step_completed',
            action: 'completed',
            summary: "Run Step completed: {$runStep->businessProcessStep?->name}",
            afterState: [
                'id' => $runStep->id,
                'business_process_run_id' => $runStep->business_process_run_id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $runStep->assignment_id,
                'status' => $runStep->status,
                'output_payload' => $runStep->output_payload,
                'completed_at' => $runStep->completed_at?->toISOString(),
            ],
        );

        $this->activityService->runStepCompleted($runStep, $auditLog);
    }

    /**
     * @return Collection<int, BusinessProcessRunStep>
     */
    private function markReadyDependentRunSteps(BusinessProcessRun $run, Assignment $assignment): Collection
    {
        $runSteps = $this->runStepsForEvaluation($run);
        $readyRunSteps = collect();

        $runSteps
            ->filter(fn (BusinessProcessRunStep $runStep): bool => $runStep->status === 'waiting_for_dependency')
            ->each(function (BusinessProcessRunStep $runStep) use ($run, $runSteps, $readyRunSteps, $assignment): void {
                if (! $this->dependenciesAreSatisfied($runStep, $runSteps)) {
                    return;
                }

                $runStep->update([
                    'status' => 'ready',
                    'ready_at' => $runStep->ready_at ?? now(),
                ]);

                $runStep->refresh();

                $event = $this->recordProcessEvent(
                    definition: $run->businessProcessDefinition,
                    run: $run,
                    runStep: $runStep,
                    eventType: 'run_step_ready',
                    summary: "Run Step ready: {$runStep->businessProcessStep?->name}",
                    payload: [
                        'business_process_run_step_id' => $runStep->id,
                        'business_process_step_id' => $runStep->business_process_step_id,
                        'dependency_rules' => $runStep->businessProcessStep?->dependency_rules ?? [],
                        'status' => $runStep->status,
                    ],
                );

                $this->recordProcessLog(
                    run: $run,
                    runStep: $runStep,
                    event: $event,
                    assignment: $assignment,
                    message: "Run Step {$runStep->id} marked ready after dependency evaluation.",
                    context: [
                        'business_process_run_step_id' => $runStep->id,
                        'business_process_step_id' => $runStep->business_process_step_id,
                        'dependency_rules' => $runStep->businessProcessStep?->dependency_rules ?? [],
                        'trigger_assignment_id' => $assignment->id,
                        'status' => $runStep->status,
                    ],
                );

                $auditLog = $this->auditLogService->record(
                    auditable: $runStep,
                    eventType: 'run_step_ready',
                    action: 'ready',
                    summary: "Run Step ready: {$runStep->businessProcessStep?->name}",
                    afterState: [
                        'id' => $runStep->id,
                        'business_process_run_id' => $runStep->business_process_run_id,
                        'business_process_step_id' => $runStep->business_process_step_id,
                        'status' => $runStep->status,
                        'ready_at' => $runStep->ready_at?->toISOString(),
                    ],
                );

                $this->activityService->runStepReady($runStep, $auditLog);
                $readyRunSteps->push($runStep);
            });

        return $readyRunSteps->values();
    }

    /**
     * @return Collection<int, BusinessProcessRunStep>
     */
    private function runStepsForEvaluation(BusinessProcessRun $run): Collection
    {
        return $run
            ->runSteps()
            ->with('businessProcessStep')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, BusinessProcessRunStep>  $runSteps
     */
    private function dependenciesAreSatisfied(BusinessProcessRunStep $candidate, Collection $runSteps): bool
    {
        $dependencyRules = $candidate->businessProcessStep?->dependency_rules ?? [];

        if ($dependencyRules === []) {
            return true;
        }

        foreach ($dependencyRules as $dependencyRule) {
            $stepKey = $dependencyRule['step_key'] ?? null;
            $requiredStatus = $dependencyRule['required_status'] ?? 'completed';

            if ($stepKey === null) {
                return false;
            }

            $dependencyRunStep = $runSteps->first(
                fn (BusinessProcessRunStep $runStep): bool => $runStep->businessProcessStep?->step_key === $stepKey,
            );

            if ($dependencyRunStep?->status !== $requiredStatus) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, BusinessProcessRunStep>  $runSteps
     */
    private function allRunStepsCompleted(Collection $runSteps): bool
    {
        return $runSteps->isNotEmpty()
            && $runSteps->every(fn (BusinessProcessRunStep $runStep): bool => $runStep->status === 'completed');
    }

    /**
     * @param  Collection<int, BusinessProcessRunStep>  $runSteps
     * @param  Collection<int, BusinessProcessRunStep>  $readyRunSteps
     */
    private function updateRunningProcessRun(BusinessProcessRun $run, Collection $runSteps, Collection $readyRunSteps): void
    {
        $completedCount = $runSteps->where('status', 'completed')->count();
        $progressPercent = (int) floor(($completedCount / max($runSteps->count(), 1)) * 100);
        $nextReadyRunStep = $readyRunSteps->first()
            ?? $runSteps->first(fn (BusinessProcessRunStep $runStep): bool => $runStep->status === 'ready');

        $run->update([
            'status' => 'running',
            'current_run_step_id' => $nextReadyRunStep?->id ?? $run->current_run_step_id,
            'progress_percent' => min($progressPercent, 99),
        ]);
    }

    private function completeProcessRun(BusinessProcessRun $run, Assignment $assignment): void
    {
        if ($run->status === 'completed') {
            return;
        }

        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percent' => 100,
        ]);

        $run->refresh();

        $event = $this->recordProcessEvent(
            definition: $run->businessProcessDefinition,
            run: $run,
            assignment: $assignment,
            eventType: 'process_run_completed',
            summary: "Process Run completed: {$run->title}",
            payload: [
                'business_process_run_id' => $run->id,
                'business_process_definition_id' => $run->business_process_definition_id,
                'assignment_id' => $assignment->id,
                'status' => $run->status,
                'progress_percent' => $run->progress_percent,
            ],
        );

        $this->recordProcessLog(
            run: $run,
            event: $event,
            assignment: $assignment,
            message: "Process Run {$run->run_key} completed.",
            context: [
                'business_process_run_id' => $run->id,
                'business_process_definition_id' => $run->business_process_definition_id,
                'assignment_id' => $assignment->id,
                'status' => $run->status,
                'progress_percent' => $run->progress_percent,
            ],
        );

        $auditLog = $this->auditLogService->record(
            auditable: $run,
            eventType: 'process_run_completed',
            action: 'completed',
            summary: "Process Run completed: {$run->title}",
            afterState: [
                'id' => $run->id,
                'business_process_definition_id' => $run->business_process_definition_id,
                'status' => $run->status,
                'progress_percent' => $run->progress_percent,
                'completed_at' => $run->completed_at?->toISOString(),
            ],
        );

        $this->activityService->processRunCompleted($run, $auditLog);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function recordProcessEvent(
        BusinessProcessDefinition $definition,
        BusinessProcessRun $run,
        string $eventType,
        string $summary,
        array $payload,
        ?BusinessProcessRunStep $runStep = null,
        ?Assignment $assignment = null,
        ?User $actor = null,
    ): ProcessEvent {
        return ProcessEvent::query()->create([
            'organization_id' => $definition->organization_id,
            'business_process_definition_id' => $definition->id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $runStep?->id,
            'assignment_id' => $assignment?->id,
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'event_key' => Str::uuid()->toString(),
            'summary' => $summary,
            'payload' => $payload,
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function recordProcessLog(
        BusinessProcessRun $run,
        string $message,
        array $context,
        ?BusinessProcessRunStep $runStep = null,
        ?ProcessEvent $event = null,
        ?Assignment $assignment = null,
    ): ProcessLog {
        return ProcessLog::query()->create([
            'organization_id' => $run->organization_id,
            'business_process_run_id' => $run->id,
            'business_process_run_step_id' => $runStep?->id,
            'process_event_id' => $event?->id,
            'assignment_id' => $assignment?->id,
            'log_level' => 'info',
            'message' => $message,
            'context' => $context,
            'created_at' => now(),
        ]);
    }
}
