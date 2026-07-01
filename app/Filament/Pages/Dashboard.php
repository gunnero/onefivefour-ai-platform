<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Services\OperationsCenter\OperationsCenterProjection;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'HQ Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'HQ Dashboard';

    protected static bool $isDiscovered = false;

    protected string $view = 'filament.pages.dashboard';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $organization = Organization::query()
            ->withCount([
                'sites',
                'departments',
                'employees',
                'assignments as active_assignments_count' => fn (Builder $query): Builder => $query->whereIn('status', self::currentAssignmentStatuses()),
                'policies as active_policies_count' => fn (Builder $query): Builder => $query->where('status', 'active'),
                'standardOperatingProcedures as active_sops_count' => fn (Builder $query): Builder => $query->where('status', 'active'),
            ])
            ->orderByRaw("case when slug = 'onefivefour' then 0 else 1 end")
            ->orderBy('name')
            ->first();

        return [
            'organization' => $organization,
            'departments' => $organization ? $this->departmentsFor($organization) : collect(),
            'employees' => $organization ? $this->employeesFor($organization) : collect(),
            'assignments' => $organization ? $this->assignmentsFor($organization) : collect(),
            'activities' => $organization ? $this->activitiesFor($organization) : collect(),
            'operationsCenter' => $organization ? app(OperationsCenterProjection::class)->forOrganization($organization) : null,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function currentAssignmentStatuses(): array
    {
        return ['pending', 'accepted', 'in_progress', 'blocked', 'needs_review'];
    }

    /**
     * @return Collection<int, Department>
     */
    private function departmentsFor(Organization $organization): Collection
    {
        return $organization->departments()
            ->withCount([
                'employees',
                'assignments as active_assignments_count' => fn (Builder $query): Builder => $query->whereIn('status', self::currentAssignmentStatuses()),
                'standardOperatingProcedures as active_sops_count' => fn (Builder $query): Builder => $query->where('status', 'active'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Employee>
     */
    private function employeesFor(Organization $organization): Collection
    {
        return $organization->employees()
            ->with([
                'department',
                'capabilities' => fn ($query) => $query
                    ->wherePivot('status', 'active')
                    ->orderBy('name'),
            ])
            ->withCount([
                'assignments as current_assignments_count' => fn (Builder $query): Builder => $query->whereIn('status', self::currentAssignmentStatuses()),
            ])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * @return Collection<int, Assignment>
     */
    private function assignmentsFor(Organization $organization): Collection
    {
        return $organization->assignments()
            ->with(['department', 'employee', 'site', 'standardOperatingProcedure'])
            ->whereIn('status', self::currentAssignmentStatuses())
            ->orderBy('due_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    private function activitiesFor(Organization $organization): Collection
    {
        return $organization->activities()
            ->with(['department', 'employee', 'assignment', 'auditLog'])
            ->where('status', 'visible')
            ->latest('occurred_at')
            ->limit(10)
            ->get();
    }
}
