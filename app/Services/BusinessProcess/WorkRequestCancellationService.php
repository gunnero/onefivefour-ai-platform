<?php

namespace App\Services\BusinessProcess;

use App\Models\Assignment;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\WorkRequest;
use App\Services\ActivityService;
use App\Services\AuditLogService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkRequestCancellationService
{
    private const CANCELLABLE_ROUTING_DECISION_STATUSES = [
        'pending',
        'evaluating',
    ];

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function cancel(WorkRequest $workRequest, ?string $reason = null): WorkRequest
    {
        $workRequest->loadMissing(['organization', 'site', 'department']);

        if ($workRequest->assignment_id !== null || Assignment::query()->where('work_request_id', $workRequest->id)->exists()) {
            throw new DomainException('Only Work Requests without an Assignment can be cancelled before dispatch.');
        }

        return DB::transaction(function () use ($workRequest, $reason): WorkRequest {
            $workRequest = WorkRequest::query()
                ->whereKey($workRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            $cancelledDecisionIds = RoutingDecision::query()
                ->where('work_request_id', $workRequest->id)
                ->whereIn('status', self::CANCELLABLE_ROUTING_DECISION_STATUSES)
                ->pluck('id');

            RoutingDecision::query()
                ->whereIn('id', $cancelledDecisionIds)
                ->update([
                    'status' => 'cancelled',
                    'failure_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $metadata = $workRequest->metadata ?? [];

            if ($reason !== null) {
                $metadata['cancellation_reason'] = $reason;
            }

            $workRequest->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => $metadata,
            ]);

            $workRequest->refresh()->loadMissing(['organization', 'site', 'department']);

            $event = ProcessEvent::query()->create([
                'organization_id' => $workRequest->organization_id,
                'business_process_definition_id' => $workRequest->business_process_definition_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'assignment_id' => null,
                'actor_type' => null,
                'actor_id' => null,
                'event_type' => 'work_request_cancelled',
                'event_key' => Str::uuid()->toString(),
                'summary' => "Work Request cancelled: {$workRequest->title}",
                'payload' => [
                    'work_request_id' => $workRequest->id,
                    'cancelled_routing_decision_ids' => $cancelledDecisionIds->values()->all(),
                    'reason' => $reason,
                    'status' => $workRequest->status,
                ],
                'occurred_at' => now(),
                'created_at' => now(),
            ]);

            if ($workRequest->business_process_run_id !== null) {
                ProcessLog::query()->create([
                    'organization_id' => $workRequest->organization_id,
                    'business_process_run_id' => $workRequest->business_process_run_id,
                    'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                    'process_event_id' => $event->id,
                    'assignment_id' => null,
                    'log_level' => 'info',
                    'message' => "Work Request {$workRequest->work_request_key} cancelled before dispatch.",
                    'context' => [
                        'work_request_id' => $workRequest->id,
                        'cancelled_routing_decision_ids' => $cancelledDecisionIds->values()->all(),
                        'reason' => $reason,
                        'status' => $workRequest->status,
                    ],
                    'created_at' => now(),
                ]);
            }

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
                    'cancelled_routing_decision_ids' => $cancelledDecisionIds->values()->all(),
                    'cancellation_reason' => $reason,
                ],
            );

            $this->activityService->workRequestCancelled($workRequest, $auditLog);

            return $workRequest->refresh();
        });
    }
}
