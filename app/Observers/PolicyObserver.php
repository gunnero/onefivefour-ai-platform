<?php

namespace App\Observers;

use App\Models\Policy;
use App\Observers\Concerns\CapturesAuditChanges;
use App\Services\ActivityService;
use App\Services\AuditLogService;

class PolicyObserver
{
    use CapturesAuditChanges;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function created(Policy $policy): void
    {
        $auditLog = $this->auditLogService->record(
            auditable: $policy,
            eventType: 'policy_created',
            action: 'created',
            summary: "Policy created: {$policy->title}",
            afterState: $this->auditState($policy),
        );

        if ($policy->status === 'active') {
            $this->activityService->policyActivated($policy, $auditLog);
        }
    }

    public function updated(Policy $policy): void
    {
        if ($policy->wasChanged('status')) {
            $changes = [
                'status' => $policy->status,
            ];

            $auditLog = $this->auditLogService->record(
                auditable: $policy,
                eventType: 'policy_status_changed',
                action: 'status_changed',
                summary: "Policy status changed: {$policy->title}",
                beforeState: $this->originalAuditState($policy, $changes),
                afterState: $changes,
            );

            if ($policy->status === 'active') {
                $this->activityService->policyActivated($policy, $auditLog);
            }

            return;
        }

        $changes = $this->changedAuditState($policy);

        if ($changes === []) {
            return;
        }

        $this->auditLogService->record(
            auditable: $policy,
            eventType: 'policy_updated',
            action: 'updated',
            summary: "Policy updated: {$policy->title}",
            beforeState: $this->originalAuditState($policy, $changes),
            afterState: $changes,
        );
    }
}
