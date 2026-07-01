<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Policy;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;

class ActivityService
{
    public function employeeCreated(Employee $employee, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $employee->organization,
            activityType: 'employee_created',
            title: "Employee created: {$employee->full_name}",
            body: "{$employee->role_title} is active in {$employee->department?->name}.",
            department: $employee->department,
            employee: $employee,
            auditLog: $auditLog,
        );
    }

    public function employeeStatusChanged(Employee $employee, AuditLog $auditLog): Activity
    {
        $status = str_replace('_', ' ', $employee->employment_status);

        return $this->record(
            organization: $employee->organization,
            activityType: 'employee_status_changed',
            title: "Employee {$status}: {$employee->full_name}",
            body: "{$employee->full_name} is now {$status}.",
            department: $employee->department,
            employee: $employee,
            auditLog: $auditLog,
        );
    }

    public function policyActivated(Policy $policy, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $policy->organization,
            activityType: 'policy_activated',
            title: "Policy activated: {$policy->title}",
            body: 'An Organization Policy is now active.',
            auditLog: $auditLog,
        );
    }

    public function sopActivated(StandardOperatingProcedure $sop, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $sop->organization,
            activityType: 'sop_activated',
            title: "SOP activated: {$sop->title}",
            body: 'A Standard Operating Procedure is now active.',
            site: $sop->site,
            department: $sop->department,
            auditLog: $auditLog,
        );
    }

    public function assignmentCreated(Assignment $assignment, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $assignment->organization,
            activityType: 'assignment_created',
            title: "Assignment created: {$assignment->title}",
            body: "Assigned to {$assignment->employee?->full_name} in {$assignment->department?->name}.",
            site: $assignment->site,
            department: $assignment->department,
            employee: $assignment->employee,
            assignment: $assignment,
            auditLog: $auditLog,
        );
    }

    public function assignmentStatusChanged(Assignment $assignment, AuditLog $auditLog): Activity
    {
        $status = str_replace('_', ' ', $assignment->status);

        return $this->record(
            organization: $assignment->organization,
            activityType: 'assignment_status_changed',
            title: "Assignment {$status}: {$assignment->title}",
            body: "Assignment status changed to {$status}.",
            site: $assignment->site,
            department: $assignment->department,
            employee: $assignment->employee,
            assignment: $assignment,
            auditLog: $auditLog,
        );
    }

    private function record(
        Organization $organization,
        string $activityType,
        string $title,
        ?string $body = null,
        ?Site $site = null,
        ?Department $department = null,
        ?Employee $employee = null,
        ?Assignment $assignment = null,
        ?AuditLog $auditLog = null,
    ): Activity {
        return Activity::query()->create([
            'organization_id' => $organization->id,
            'site_id' => $site?->id,
            'department_id' => $department?->id,
            'employee_id' => $employee?->id,
            'assignment_id' => $assignment?->id,
            'audit_log_id' => $auditLog?->id,
            'activity_type' => $activityType,
            'status' => 'visible',
            'title' => $title,
            'body' => $body,
            'metadata' => [],
            'occurred_at' => now(),
        ]);
    }
}
