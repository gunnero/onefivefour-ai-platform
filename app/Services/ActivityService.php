<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Policy;
use App\Models\RoutingDecision;
use App\Models\Site;
use App\Models\StandardOperatingProcedure;
use App\Models\WorkRequest;

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

    public function processRunStarted(BusinessProcessRun $run, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $run->organization,
            activityType: 'process_run_started',
            title: "Process Run started: {$run->title}",
            body: "Business Process started from {$run->businessProcessDefinition?->name}.",
            site: $run->site,
            auditLog: $auditLog,
            metadata: [
                'business_process_definition_id' => $run->business_process_definition_id,
                'business_process_run_id' => $run->id,
            ],
        );
    }

    public function runStepCompleted(BusinessProcessRunStep $runStep, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $runStep->organization,
            activityType: 'run_step_completed',
            title: "Run Step completed: {$runStep->businessProcessStep?->name}",
            body: 'A Process Run Step was completed from a linked Assignment.',
            department: $runStep->department,
            assignment: $runStep->assignment,
            auditLog: $auditLog,
            metadata: [
                'business_process_run_id' => $runStep->business_process_run_id,
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $runStep->assignment_id,
                'status' => $runStep->status,
            ],
        );
    }

    public function runStepReady(BusinessProcessRunStep $runStep, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $runStep->organization,
            activityType: 'run_step_ready',
            title: "Run Step ready: {$runStep->businessProcessStep?->name}",
            body: 'Dependencies are satisfied and the Run Step can create a Work Request.',
            department: $runStep->department,
            auditLog: $auditLog,
            metadata: [
                'business_process_run_id' => $runStep->business_process_run_id,
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'status' => $runStep->status,
            ],
        );
    }

    public function processRunCompleted(BusinessProcessRun $run, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $run->organization,
            activityType: 'process_run_completed',
            title: "Process Run completed: {$run->title}",
            body: "Business Process completed from {$run->businessProcessDefinition?->name}.",
            site: $run->site,
            auditLog: $auditLog,
            metadata: [
                'business_process_definition_id' => $run->business_process_definition_id,
                'business_process_run_id' => $run->id,
                'status' => $run->status,
                'progress_percent' => $run->progress_percent,
            ],
        );
    }

    public function runStepBlocked(BusinessProcessRunStep $runStep, AuditLog $auditLog): Activity
    {
        return $this->recordRunStepRuntimeActivity(
            runStep: $runStep,
            auditLog: $auditLog,
            activityType: 'run_step_blocked',
            title: "Run Step blocked: {$runStep->businessProcessStep?->name}",
            body: $runStep->blocked_reason,
        );
    }

    public function processRunBlocked(BusinessProcessRun $run, AuditLog $auditLog): Activity
    {
        return $this->recordProcessRunRuntimeActivity(
            run: $run,
            auditLog: $auditLog,
            activityType: 'process_run_blocked',
            title: "Process Run blocked: {$run->title}",
            body: ($run->metadata ?? [])['blocked_reason'] ?? null,
        );
    }

    public function runStepFailed(BusinessProcessRunStep $runStep, AuditLog $auditLog): Activity
    {
        return $this->recordRunStepRuntimeActivity(
            runStep: $runStep,
            auditLog: $auditLog,
            activityType: 'run_step_failed',
            title: "Run Step failed: {$runStep->businessProcessStep?->name}",
            body: $runStep->failure_reason,
        );
    }

    public function processRunFailed(BusinessProcessRun $run, AuditLog $auditLog): Activity
    {
        return $this->recordProcessRunRuntimeActivity(
            run: $run,
            auditLog: $auditLog,
            activityType: 'process_run_failed',
            title: "Process Run failed: {$run->title}",
            body: ($run->metadata ?? [])['failure_reason'] ?? null,
        );
    }

    public function runStepCancelled(BusinessProcessRunStep $runStep, AuditLog $auditLog, ?string $reason = null): Activity
    {
        return $this->recordRunStepRuntimeActivity(
            runStep: $runStep,
            auditLog: $auditLog,
            activityType: 'run_step_cancelled',
            title: "Run Step cancelled: {$runStep->businessProcessStep?->name}",
            body: $reason,
        );
    }

    public function processRunCancelled(BusinessProcessRun $run, AuditLog $auditLog): Activity
    {
        return $this->recordProcessRunRuntimeActivity(
            run: $run,
            auditLog: $auditLog,
            activityType: 'process_run_cancelled',
            title: "Process Run cancelled: {$run->title}",
            body: ($run->metadata ?? [])['cancellation_reason'] ?? null,
        );
    }

    public function workRequestCancelled(WorkRequest $workRequest, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $workRequest->organization,
            activityType: 'work_request_cancelled',
            title: "Work Request cancelled: {$workRequest->title}",
            body: ($workRequest->metadata ?? [])['cancellation_reason'] ?? null,
            site: $workRequest->site,
            department: $workRequest->department,
            auditLog: $auditLog,
            metadata: [
                'business_process_definition_id' => $workRequest->business_process_definition_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'work_request_id' => $workRequest->id,
                'status' => $workRequest->status,
            ],
        );
    }

    public function workRequestCreated(WorkRequest $workRequest, AuditLog $auditLog): Activity
    {
        return $this->record(
            organization: $workRequest->organization,
            activityType: 'work_request_created',
            title: "Work Request created: {$workRequest->title}",
            body: "Work requested for {$workRequest->department?->name}.",
            site: $workRequest->site,
            department: $workRequest->department,
            auditLog: $auditLog,
            metadata: [
                'business_process_definition_id' => $workRequest->business_process_definition_id,
                'business_process_run_id' => $workRequest->business_process_run_id,
                'business_process_run_step_id' => $workRequest->business_process_run_step_id,
                'assignment_template_id' => $workRequest->assignment_template_id,
                'work_request_id' => $workRequest->id,
            ],
        );
    }

    public function routingDecisionRecorded(RoutingDecision $decision, AuditLog $auditLog): Activity
    {
        $activityType = match ($decision->status) {
            'selected' => 'routing_decision_selected',
            'manual_required' => 'routing_manual_required',
            'no_eligible_employee' => 'routing_failed',
            default => 'routing_decision_recorded',
        };

        $title = match ($decision->status) {
            'selected' => "Routing selected Employee: {$decision->workRequest?->title}",
            'manual_required' => "Routing requires manual selection: {$decision->workRequest?->title}",
            'no_eligible_employee' => "Routing failed: {$decision->workRequest?->title}",
            default => "Routing Decision recorded: {$decision->workRequest?->title}",
        };

        return $this->record(
            organization: $decision->organization,
            activityType: $activityType,
            title: $title,
            body: $decision->decision_reason ?? $decision->failure_reason,
            site: $decision->site,
            department: $decision->department,
            employee: $decision->selectedEmployee,
            auditLog: $auditLog,
            metadata: [
                'work_request_id' => $decision->work_request_id,
                'routing_decision_id' => $decision->id,
                'selected_employee_id' => $decision->selected_employee_id,
                'strategy' => $decision->strategy,
                'status' => $decision->status,
            ],
        );
    }

    private function recordRunStepRuntimeActivity(
        BusinessProcessRunStep $runStep,
        AuditLog $auditLog,
        string $activityType,
        string $title,
        ?string $body = null,
    ): Activity {
        return $this->record(
            organization: $runStep->organization,
            activityType: $activityType,
            title: $title,
            body: $body,
            department: $runStep->department,
            assignment: $runStep->assignment,
            auditLog: $auditLog,
            metadata: [
                'business_process_run_id' => $runStep->business_process_run_id,
                'business_process_run_step_id' => $runStep->id,
                'business_process_step_id' => $runStep->business_process_step_id,
                'assignment_id' => $runStep->assignment_id,
                'status' => $runStep->status,
            ],
        );
    }

    private function recordProcessRunRuntimeActivity(
        BusinessProcessRun $run,
        AuditLog $auditLog,
        string $activityType,
        string $title,
        ?string $body = null,
    ): Activity {
        return $this->record(
            organization: $run->organization,
            activityType: $activityType,
            title: $title,
            body: $body,
            site: $run->site,
            auditLog: $auditLog,
            metadata: [
                'business_process_definition_id' => $run->business_process_definition_id,
                'business_process_run_id' => $run->id,
                'status' => $run->status,
                'progress_percent' => $run->progress_percent,
            ],
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
        array $metadata = [],
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
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
