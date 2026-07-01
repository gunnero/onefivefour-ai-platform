<?php

namespace App\Services\OperationsCenter;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\DepartmentQueue;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\RoutingDecision;
use App\Models\WorkRequest;
use Illuminate\Support\Collection;

class OperationsCenterProjection
{
    /**
     * @return array<string, mixed>
     */
    public function forOrganization(Organization $organization): array
    {
        return [
            'quickStats' => $this->quickStats($organization),
            'activeBusinessProcesses' => $this->activeBusinessProcesses($organization),
            'currentProcessRuns' => $this->currentProcessRuns($organization),
            'departmentQueues' => $this->departmentQueues($organization),
            'workRequestCounts' => $this->workRequestCounts($organization),
            'routingDecisions' => $this->routingDecisions($organization),
            'operationsFeed' => $this->operationsFeed($organization),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function quickStats(Organization $organization): array
    {
        return [
            'running_processes' => BusinessProcessRun::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'running')
                ->count(),
            'ready_steps' => BusinessProcessRunStep::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'ready')
                ->count(),
            'pending_work_requests' => WorkRequest::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'pending')
                ->count(),
            'assignments' => Assignment::query()
                ->where('organization_id', $organization->id)
                ->whereIn('status', $this->currentAssignmentStatuses())
                ->count(),
            'blocked_runs' => BusinessProcessRun::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'blocked')
                ->count(),
            'failed_runs' => BusinessProcessRun::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'failed')
                ->count(),
            'waiting_approval' => BusinessProcessRun::query()
                ->where('organization_id', $organization->id)
                ->where('status', 'waiting_for_approval')
                ->count(),
        ];
    }

    /**
     * @return Collection<int, BusinessProcessDefinition>
     */
    private function activeBusinessProcesses(Organization $organization): Collection
    {
        return BusinessProcessDefinition::query()
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with('owningDepartment')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->each(function (BusinessProcessDefinition $definition): void {
                $definition->currentProcessRun = BusinessProcessRun::query()
                    ->where('business_process_definition_id', $definition->id)
                    ->with('currentRunStep.businessProcessStep')
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->latest('started_at')
                    ->latest('id')
                    ->first();
            });
    }

    /**
     * @return Collection<int, BusinessProcessRun>
     */
    private function currentProcessRuns(Organization $organization): Collection
    {
        return BusinessProcessRun::query()
            ->where('organization_id', $organization->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with([
                'businessProcessDefinition',
                'currentRunStep.department',
                'currentRunStep.employee',
                'currentRunStep.assignment.employee',
            ])
            ->latest('started_at')
            ->latest('id')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, DepartmentQueue>
     */
    private function departmentQueues(Organization $organization): Collection
    {
        return DepartmentQueue::query()
            ->where('organization_id', $organization->id)
            ->with('department')
            ->orderBy('name')
            ->get()
            ->each(function (DepartmentQueue $queue) use ($organization): void {
                $baseQuery = WorkRequest::query()
                    ->where('organization_id', $organization->id)
                    ->where('department_id', $queue->department_id);

                if ($queue->site_id !== null) {
                    $baseQuery->where('site_id', $queue->site_id);
                }

                $queue->pending_work_requests = (clone $baseQuery)->where('status', 'pending')->count();
                $queue->routed_work_requests = (clone $baseQuery)->where('status', 'routed')->count();
                $queue->blocked_work_requests = (clone $baseQuery)->where('status', 'blocked')->count();
                $queue->failed_work_requests = (clone $baseQuery)->where('status', 'failed')->count();
            });
    }

    /**
     * @return array<string, int>
     */
    private function workRequestCounts(Organization $organization): array
    {
        return collect([
            'pending',
            'routed',
            'waiting_for_manual_selection',
            'blocked',
            'cancelled',
        ])->mapWithKeys(fn (string $status): array => [
            $status => WorkRequest::query()
                ->where('organization_id', $organization->id)
                ->where('status', $status)
                ->count(),
        ])->all();
    }

    /**
     * @return Collection<int, RoutingDecision>
     */
    private function routingDecisions(Organization $organization): Collection
    {
        return RoutingDecision::query()
            ->where('organization_id', $organization->id)
            ->with(['selectedEmployee', 'workRequest'])
            ->latest('decided_at')
            ->latest('id')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function operationsFeed(Organization $organization): Collection
    {
        $processEvents = ProcessEvent::query()
            ->where('organization_id', $organization->id)
            ->with(['businessProcessRun', 'businessProcessRunStep.businessProcessStep', 'assignment'])
            ->latest('occurred_at')
            ->limit(10)
            ->get()
            ->toBase()
            ->map(fn (ProcessEvent $event): array => [
                'source' => 'process_event',
                'source_label' => 'Process Event',
                'occurred_at' => $event->occurred_at,
                'type' => $event->event_type,
                'title' => $event->summary,
                'related' => collect([
                    $event->businessProcessRun?->title,
                    $event->businessProcessRunStep?->businessProcessStep?->name,
                    $event->assignment?->title,
                ])->filter()->join(' / '),
            ]);

        $activities = Activity::query()
            ->where('organization_id', $organization->id)
            ->where('status', 'visible')
            ->with(['department', 'employee', 'assignment'])
            ->latest('occurred_at')
            ->limit(10)
            ->get()
            ->toBase()
            ->map(fn (Activity $activity): array => [
                'source' => 'activity',
                'source_label' => 'Activity',
                'occurred_at' => $activity->occurred_at,
                'type' => $activity->activity_type,
                'title' => $activity->title,
                'related' => collect([
                    $activity->employee?->full_name,
                    $activity->assignment?->title,
                    $activity->department?->name,
                ])->filter()->join(' / '),
            ]);

        return $processEvents
            ->merge($activities)
            ->sortByDesc('occurred_at')
            ->values()
            ->take(15);
    }

    /**
     * @return array<int, string>
     */
    private function currentAssignmentStatuses(): array
    {
        return ['pending', 'accepted', 'in_progress', 'blocked', 'needs_review'];
    }
}
