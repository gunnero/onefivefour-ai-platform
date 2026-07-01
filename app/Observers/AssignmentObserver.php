<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Observers\Concerns\CapturesAuditChanges;
use App\Services\ActivityService;
use App\Services\AuditLogService;

class AssignmentObserver
{
    use CapturesAuditChanges;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function created(Assignment $assignment): void
    {
        $auditLog = $this->auditLogService->record(
            auditable: $assignment,
            eventType: 'assignment_created',
            action: 'created',
            summary: "Assignment created: {$assignment->title}",
            afterState: $this->auditState($assignment),
        );

        $this->activityService->assignmentCreated($assignment, $auditLog);
    }

    public function updated(Assignment $assignment): void
    {
        if ($assignment->wasChanged('status')) {
            $changes = $this->changedAuditState($assignment);

            $auditLog = $this->auditLogService->record(
                auditable: $assignment,
                eventType: 'assignment_status_changed',
                action: 'status_changed',
                summary: "Assignment status changed: {$assignment->title}",
                beforeState: $this->originalAuditState($assignment, $changes),
                afterState: $changes,
            );

            $this->activityService->assignmentStatusChanged($assignment, $auditLog);

            return;
        }

        $changes = $this->changedAuditState($assignment);

        if ($changes === []) {
            return;
        }

        $this->auditLogService->record(
            auditable: $assignment,
            eventType: 'assignment_updated',
            action: 'updated',
            summary: "Assignment updated: {$assignment->title}",
            beforeState: $this->originalAuditState($assignment, $changes),
            afterState: $changes,
        );
    }
}
