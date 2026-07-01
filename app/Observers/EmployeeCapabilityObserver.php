<?php

namespace App\Observers;

use App\Models\EmployeeCapability;
use App\Observers\Concerns\CapturesAuditChanges;
use App\Services\AuditLogService;

class EmployeeCapabilityObserver
{
    use CapturesAuditChanges;

    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function created(EmployeeCapability $employeeCapability): void
    {
        $employeeCapability->loadMissing(['employee', 'capability']);

        $this->auditLogService->record(
            auditable: $employeeCapability,
            eventType: 'employee_capability_granted',
            action: 'granted',
            summary: "Capability granted: {$employeeCapability->capability?->name} to {$employeeCapability->employee?->full_name}",
            afterState: $this->auditState($employeeCapability),
        );
    }

    public function updated(EmployeeCapability $employeeCapability): void
    {
        $changes = $this->changedAuditState($employeeCapability);

        if ($changes === []) {
            return;
        }

        $employeeCapability->loadMissing(['employee', 'capability']);

        if (
            ! array_key_exists('status', $changes)
            && ! array_key_exists('revoked_at', $changes)
        ) {
            return;
        }

        if (($employeeCapability->status === 'active') && ($employeeCapability->revoked_at === null)) {
            $this->auditLogService->record(
                auditable: $employeeCapability,
                eventType: 'employee_capability_granted',
                action: 'granted',
                summary: "Capability granted: {$employeeCapability->capability?->name} to {$employeeCapability->employee?->full_name}",
                beforeState: $this->originalAuditState($employeeCapability, $changes),
                afterState: $changes,
            );

            return;
        }

        $this->auditLogService->record(
            auditable: $employeeCapability,
            eventType: 'employee_capability_revoked',
            action: 'revoked',
            summary: "Capability revoked: {$employeeCapability->capability?->name} from {$employeeCapability->employee?->full_name}",
            beforeState: $this->originalAuditState($employeeCapability, $changes),
            afterState: $changes,
        );
    }
}
