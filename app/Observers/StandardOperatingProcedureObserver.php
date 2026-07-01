<?php

namespace App\Observers;

use App\Models\StandardOperatingProcedure;
use App\Observers\Concerns\CapturesAuditChanges;
use App\Services\ActivityService;
use App\Services\AuditLogService;

class StandardOperatingProcedureObserver
{
    use CapturesAuditChanges;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function created(StandardOperatingProcedure $sop): void
    {
        $auditLog = $this->auditLogService->record(
            auditable: $sop,
            eventType: 'sop_created',
            action: 'created',
            summary: "SOP created: {$sop->title}",
            afterState: $this->auditState($sop),
        );

        if ($sop->status === 'active') {
            $this->activityService->sopActivated($sop, $auditLog);
        }
    }

    public function updated(StandardOperatingProcedure $sop): void
    {
        if ($sop->wasChanged('status')) {
            $changes = [
                'status' => $sop->status,
            ];

            $auditLog = $this->auditLogService->record(
                auditable: $sop,
                eventType: 'sop_status_changed',
                action: 'status_changed',
                summary: "SOP status changed: {$sop->title}",
                beforeState: $this->originalAuditState($sop, $changes),
                afterState: $changes,
            );

            if ($sop->status === 'active') {
                $this->activityService->sopActivated($sop, $auditLog);
            }

            return;
        }

        $changes = $this->changedAuditState($sop);

        if ($changes === []) {
            return;
        }

        $this->auditLogService->record(
            auditable: $sop,
            eventType: 'sop_updated',
            action: 'updated',
            summary: "SOP updated: {$sop->title}",
            beforeState: $this->originalAuditState($sop, $changes),
            afterState: $changes,
        );
    }
}
