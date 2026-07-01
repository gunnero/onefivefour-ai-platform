<?php

namespace App\Services\BusinessProcess;

use App\Models\Assignment;
use App\Models\DepartmentQueue;
use App\Models\Employee;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use App\Models\RoutingDecision;
use App\Models\WorkRequest;
use App\Services\ActivityService;
use App\Services\AuditLogService;
use DomainException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkRouter
{
    private const ACTIVE_ASSIGNMENT_STATUSES = [
        'pending',
        'accepted',
        'in_progress',
        'blocked',
        'needs_review',
    ];

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ActivityService $activityService,
    ) {}

    public function route(WorkRequest $workRequest): RoutingDecision
    {
        $workRequest->loadMissing(['organization', 'site', 'department', 'requiredCapability']);

        if ($workRequest->status !== 'pending') {
            throw new DomainException('Only pending Work Requests can be routed.');
        }

        return DB::transaction(function () use ($workRequest): RoutingDecision {
            $workRequest->update(['routing_started_at' => now()]);

            $candidates = $this->candidateEmployees($workRequest);
            $eligibilityResults = $this->eligibilityResults($workRequest, $candidates);
            $eligibleEmployees = $eligibilityResults
                ->filter(fn (array $result): bool => $result['eligible'])
                ->map(fn (array $result): Employee => $result['employee'])
                ->values();

            $strategy = $workRequest->routing_strategy;
            $selectedEmployee = null;
            $status = 'selected';
            $decisionReason = null;
            $failureReason = null;

            if ($strategy === 'manual') {
                $status = 'manual_required';
                $decisionReason = 'Manual routing requires a human or Manager to choose an Employee.';
                $workRequest->update(['status' => 'waiting_for_manual_selection']);
            } elseif ($eligibleEmployees->isEmpty()) {
                $status = 'no_eligible_employee';
                $failureReason = 'No eligible Employee matched the Work Request.';
                $workRequest->update([
                    'status' => 'blocked',
                    'blocked_reason' => $failureReason,
                    'blocked_at' => now(),
                ]);
            } else {
                [$selectedEmployee, $decisionReason] = $this->selectEmployee($workRequest, $strategy, $eligibleEmployees);

                $workRequest->update([
                    'status' => 'routed',
                    'routed_at' => now(),
                ]);
            }

            $decision = RoutingDecision::query()->create([
                'organization_id' => $workRequest->organization_id,
                'work_request_id' => $workRequest->id,
                'department_id' => $workRequest->department_id,
                'site_id' => $workRequest->site_id,
                'assignment_id' => null,
                'selected_employee_id' => $selectedEmployee?->id,
                'strategy' => $strategy,
                'status' => $status,
                'candidate_count' => $candidates->count(),
                'eligible_count' => $eligibleEmployees->count(),
                'candidate_snapshot' => $this->candidateSnapshot($candidates),
                'eligibility_results' => $eligibilityResults
                    ->map(fn (array $result): array => [
                        'employee_id' => $result['employee']->id,
                        'eligible' => $result['eligible'],
                        'reasons' => $result['reasons'],
                    ])
                    ->values()
                    ->all(),
                'decision_reason' => $decisionReason,
                'failure_reason' => $failureReason,
                'manager_override' => false,
                'override_reason' => null,
                'decided_by_type' => null,
                'decided_by_id' => null,
                'decided_at' => now(),
            ]);

            $this->recordSideEffects($workRequest->refresh(), $decision);

            return $decision->refresh();
        });
    }

    /**
     * @return EloquentCollection<int, Employee>
     */
    private function candidateEmployees(WorkRequest $workRequest): EloquentCollection
    {
        return Employee::query()
            ->where('organization_id', $workRequest->organization_id)
            ->where('department_id', $workRequest->department_id)
            ->with('employeeCapabilities')
            ->orderBy('full_name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  EloquentCollection<int, Employee>  $candidates
     */
    private function eligibilityResults(WorkRequest $workRequest, EloquentCollection $candidates): Collection
    {
        return $candidates->map(function (Employee $employee) use ($workRequest): array {
            $reasons = [];

            if ($employee->organization_id !== $workRequest->organization_id) {
                $reasons[] = 'different_organization';
            }

            if ($employee->department_id !== $workRequest->department_id) {
                $reasons[] = 'different_department';
            }

            if ($employee->employment_status !== 'active') {
                $reasons[] = 'inactive_employment_status';
            }

            if ($employee->paused_at !== null) {
                $reasons[] = 'paused';
            }

            if ($employee->retired_at !== null) {
                $reasons[] = 'retired';
            }

            if ($employee->archived_at !== null) {
                $reasons[] = 'archived';
            }

            if ($workRequest->required_capability_id !== null && ! $this->hasRequiredCapability($employee, $workRequest)) {
                $reasons[] = 'missing_required_capability';
            }

            return [
                'employee' => $employee,
                'eligible' => $reasons === [],
                'reasons' => $reasons,
            ];
        });
    }

    private function hasRequiredCapability(Employee $employee, WorkRequest $workRequest): bool
    {
        return $employee->employeeCapabilities->contains(function ($employeeCapability) use ($workRequest): bool {
            return $employeeCapability->organization_id === $workRequest->organization_id
                && $employeeCapability->capability_id === $workRequest->required_capability_id
                && $employeeCapability->status === 'active'
                && $employeeCapability->revoked_at === null;
        });
    }

    /**
     * @param  Collection<int, Employee>  $eligibleEmployees
     * @return array{0: Employee, 1: string}
     */
    private function selectEmployee(WorkRequest $workRequest, string $strategy, Collection $eligibleEmployees): array
    {
        return match ($strategy) {
            'first_available' => [
                $eligibleEmployees->first(),
                'Selected first eligible Employee in stable order.',
            ],
            'least_busy' => [
                $eligibleEmployees
                    ->sortBy([
                        fn (Employee $employee): int => $this->activeAssignmentCount($employee),
                        fn (Employee $employee): string => $employee->full_name,
                        fn (Employee $employee): int => $employee->id,
                    ])
                    ->values()
                    ->first(),
                'Selected eligible Employee with the lowest active Assignment count.',
            ],
            'round_robin' => [
                $this->selectRoundRobin($workRequest, $eligibleEmployees),
                'Selected next eligible Employee from Department Queue rotation.',
            ],
            'capability_match' => [
                $eligibleEmployees->first(),
                'Selected eligible Employee with required active Capability.',
            ],
            default => throw new DomainException("Unsupported Routing Strategy: {$strategy}."),
        };
    }

    private function activeAssignmentCount(Employee $employee): int
    {
        return Assignment::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', self::ACTIVE_ASSIGNMENT_STATUSES)
            ->count();
    }

    /**
     * @param  Collection<int, Employee>  $eligibleEmployees
     */
    private function selectRoundRobin(WorkRequest $workRequest, Collection $eligibleEmployees): Employee
    {
        $queue = DepartmentQueue::query()
            ->where('organization_id', $workRequest->organization_id)
            ->where('department_id', $workRequest->department_id)
            ->where(function ($query) use ($workRequest): void {
                $query->where('site_id', $workRequest->site_id)
                    ->orWhereNull('site_id');
            })
            ->orderByRaw('case when site_id = ? then 0 else 1 end', [$workRequest->site_id])
            ->orderBy('id')
            ->first();

        $selected = $eligibleEmployees->first();

        if ($queue !== null && $queue->last_selected_employee_id !== null) {
            $lastIndex = $eligibleEmployees->search(
                fn (Employee $employee): bool => $employee->id === $queue->last_selected_employee_id,
            );

            if ($lastIndex !== false) {
                $selected = $eligibleEmployees->get(($lastIndex + 1) % $eligibleEmployees->count());
            }
        }

        if ($queue !== null) {
            $queue->update(['last_selected_employee_id' => $selected->id]);
        }

        return $selected;
    }

    /**
     * @param  EloquentCollection<int, Employee>  $candidates
     * @return array<int, array<string, mixed>>
     */
    private function candidateSnapshot(EloquentCollection $candidates): array
    {
        return $candidates
            ->map(fn (Employee $employee): array => [
                'employee_id' => $employee->id,
                'full_name' => $employee->full_name,
                'department_id' => $employee->department_id,
                'employment_status' => $employee->employment_status,
            ])
            ->values()
            ->all();
    }

    private function recordSideEffects(WorkRequest $workRequest, RoutingDecision $decision): void
    {
        $eventType = match ($decision->status) {
            'selected' => 'routing_decision_created',
            'manual_required' => 'routing_manual_required',
            'no_eligible_employee' => 'routing_failed',
            default => 'routing_decision_recorded',
        };

        $event = ProcessEvent::query()->create([
            'organization_id' => $workRequest->organization_id,
            'business_process_definition_id' => $workRequest->business_process_definition_id,
            'business_process_run_id' => $workRequest->business_process_run_id,
            'business_process_run_step_id' => $workRequest->business_process_run_step_id,
            'assignment_id' => null,
            'actor_type' => null,
            'actor_id' => null,
            'event_type' => $eventType,
            'event_key' => Str::uuid()->toString(),
            'summary' => $decision->decision_reason ?? $decision->failure_reason ?? 'Routing Decision recorded.',
            'payload' => [
                'work_request_id' => $workRequest->id,
                'routing_decision_id' => $decision->id,
                'status' => $decision->status,
                'strategy' => $decision->strategy,
                'selected_employee_id' => $decision->selected_employee_id,
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
                'message' => "Routing Decision {$decision->id} recorded for Work Request {$workRequest->work_request_key}.",
                'context' => [
                    'work_request_id' => $workRequest->id,
                    'routing_decision_id' => $decision->id,
                    'status' => $decision->status,
                    'strategy' => $decision->strategy,
                    'candidate_count' => $decision->candidate_count,
                    'eligible_count' => $decision->eligible_count,
                ],
                'created_at' => now(),
            ]);
        }

        $auditEventType = match ($decision->status) {
            'selected' => 'routing_decision_selected',
            'manual_required' => 'routing_manual_required',
            'no_eligible_employee' => 'routing_failed',
            default => 'routing_decision_recorded',
        };

        $auditLog = $this->auditLogService->record(
            auditable: $decision,
            eventType: $auditEventType,
            action: $decision->status,
            summary: $decision->decision_reason ?? $decision->failure_reason ?? 'Routing Decision recorded.',
            afterState: [
                'id' => $decision->id,
                'work_request_id' => $decision->work_request_id,
                'strategy' => $decision->strategy,
                'status' => $decision->status,
                'selected_employee_id' => $decision->selected_employee_id,
                'candidate_count' => $decision->candidate_count,
                'eligible_count' => $decision->eligible_count,
            ],
        );

        $this->activityService->routingDecisionRecorded($decision, $auditLog);
    }
}
