<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $site = Site::query()->where('organization_id', $organization->id)->where('slug', 'razbudise-mk')->firstOrFail();
        $department = Department::query()->where('organization_id', $organization->id)->where('name', 'Editorial')->firstOrFail();
        $employee = Employee::query()->where('organization_id', $organization->id)->where('full_name', 'Elena Markova')->firstOrFail();
        $assignment = Assignment::query()->where('organization_id', $organization->id)->where('title', 'Review first Razbudise editorial package')->firstOrFail();
        $auditLog = AuditLog::query()->where('organization_id', $organization->id)->where('auditable_id', $assignment->id)->firstOrFail();

        Activity::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'assignment_id' => $assignment->id,
                'activity_type' => 'assignment_created',
            ],
            [
                'site_id' => $site->id,
                'department_id' => $department->id,
                'employee_id' => $employee->id,
                'audit_log_id' => $auditLog->id,
                'status' => 'visible',
                'title' => 'Assignment created for Elena Markova',
                'body' => 'Sprint 001 seed Assignment is ready for editorial review.',
                'metadata' => [],
                'occurred_at' => now(),
            ],
        );
    }
}
