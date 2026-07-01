<?php

namespace Database\Seeders;

use App\Models\Capability;
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class EmployeeCapabilitySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();

        $assignments = [
            'Elena Markova' => 'Editing',
            'Martin Nikolovski' => 'Research',
            'Mila Andonova' => 'Writing',
            'Sara Ilieva' => 'Localization',
            'Viktor Petrov' => 'SEO',
            'David Kostovski' => 'Fact Checking',
        ];

        foreach ($assignments as $employeeName => $capabilityName) {
            $employee = Employee::query()->where('organization_id', $organization->id)->where('full_name', $employeeName)->firstOrFail();
            $capability = Capability::query()->where('name', $capabilityName)->firstOrFail();

            EmployeeCapability::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'capability_id' => $capability->id,
                ],
                [
                    'organization_id' => $organization->id,
                    'status' => 'active',
                    'level' => 'standard',
                    'notes' => null,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ],
            );
        }
    }
}
