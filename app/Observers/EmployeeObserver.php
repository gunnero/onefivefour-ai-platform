<?php

namespace App\Observers;

use App\Models\Employee;
use App\Observers\Concerns\CapturesAuditChanges;
use App\Services\ActivityService;
use App\Services\AuditLogService;

class EmployeeObserver
{
    use CapturesAuditChanges;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function created(Employee $employee): void
    {
        $auditLog = $this->auditLogService->record(
            auditable: $employee,
            eventType: 'employee_created',
            action: 'created',
            summary: "Employee created: {$employee->full_name}",
            afterState: $this->auditState($employee),
        );

        $this->activityService->employeeCreated($employee, $auditLog);
    }

    public function updated(Employee $employee): void
    {
        if ($employee->wasChanged('employment_status')) {
            $changes = [
                'employment_status' => $employee->employment_status,
            ];

            $auditLog = $this->auditLogService->record(
                auditable: $employee,
                eventType: 'employee_status_changed',
                action: 'status_changed',
                summary: "Employee status changed: {$employee->full_name}",
                beforeState: $this->originalAuditState($employee, $changes),
                afterState: $changes,
            );

            $this->activityService->employeeStatusChanged($employee, $auditLog);

            return;
        }

        $changes = $this->changedAuditState($employee);

        if ($changes === []) {
            return;
        }

        $this->auditLogService->record(
            auditable: $employee,
            eventType: 'employee_updated',
            action: 'updated',
            summary: "Employee updated: {$employee->full_name}",
            beforeState: $this->originalAuditState($employee, $changes),
            afterState: $changes,
        );
    }
}
