<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();
        $employee = Employee::query()->where('organization_id', $organization->id)->where('full_name', 'Elena Markova')->firstOrFail();
        $assignment = Assignment::query()->where('organization_id', $organization->id)->where('title', 'Review first Razbudise editorial package')->firstOrFail();

        AuditLog::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'event_type' => 'assignment_created',
                'auditable_type' => Assignment::class,
                'auditable_id' => $assignment->id,
            ],
            [
                'actor_type' => Employee::class,
                'actor_id' => $employee->id,
                'action' => 'created',
                'summary' => 'Seed Assignment created for Sprint 001 organizational core.',
                'before_state' => null,
                'after_state' => ['status' => $assignment->status],
                'reason' => 'Initial Sprint 001 seed data.',
                'metadata' => [],
                'occurred_at' => now(),
            ],
        );
    }
}
