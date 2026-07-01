<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\AuditLog;
use App\Models\Employee;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected string $view = 'filament.resources.employees.pages.view-employee';

    public function getTitle(): string|Htmlable
    {
        return 'Employee Profile';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Employee $employee */
        $employee = $this->getRecord();

        $employee->loadMissing([
            'organization',
            'department',
            'manager',
            'employeeCapabilities.capability',
        ]);

        $activeAssignmentStatuses = ['pending', 'accepted', 'in_progress'];

        $assignmentQuery = fn () => $employee->assignments()
            ->with(['department', 'site', 'standardOperatingProcedure'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        $stats = [
            'Total Assignments' => $employee->assignments()->count(),
            'Active Assignments' => $employee->assignments()->whereIn('status', $activeAssignmentStatuses)->count(),
            'Completed Assignments' => $employee->assignments()->where('status', 'completed')->count(),
            'Blocked Assignments' => $employee->assignments()->where('status', 'blocked')->count(),
            'Needs Review Assignments' => $employee->assignments()->where('status', 'needs_review')->count(),
            'Capabilities Count' => $employee->employeeCapabilities()->where('status', 'active')->count(),
        ];

        return [
            'employee' => $employee,
            'organization' => $employee->organization,
            'stats' => $stats,
            'capabilities' => $employee->employeeCapabilities
                ->where('status', 'active')
                ->sortByDesc('granted_at')
                ->values(),
            'assignmentSections' => [
                'Current Assignments' => $assignmentQuery()->whereIn('status', $activeAssignmentStatuses)->limit(8)->get(),
                'Blocked Assignments' => $assignmentQuery()->where('status', 'blocked')->limit(8)->get(),
                'Needs Review Assignments' => $assignmentQuery()->where('status', 'needs_review')->limit(8)->get(),
                'Completed Assignments' => $assignmentQuery()->where('status', 'completed')->limit(8)->get(),
            ],
            'activities' => $employee->activities()
                ->with(['department', 'assignment', 'auditLog'])
                ->latest('occurred_at')
                ->limit(10)
                ->get(),
            'auditLogs' => AuditLog::query()
                ->where('organization_id', $employee->organization_id)
                ->where(function (Builder $query) use ($employee): void {
                    $query
                        ->where(function (Builder $query) use ($employee): void {
                            $query
                                ->where('auditable_type', Employee::class)
                                ->where('auditable_id', $employee->id);
                        })
                        ->orWhere(function (Builder $query) use ($employee): void {
                            $query
                                ->where('actor_type', Employee::class)
                                ->where('actor_id', $employee->id);
                        });
                })
                ->latest('occurred_at')
                ->limit(10)
                ->get(),
        ];
    }
}
