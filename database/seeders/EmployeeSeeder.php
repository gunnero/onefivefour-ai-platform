<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'onefivefour')->firstOrFail();

        $employees = [
            ['employee_code' => 'ELENA-MARKOVA', 'full_name' => 'Elena Markova', 'department' => 'Editorial', 'role_title' => 'Editor-in-Chief AI', 'mission' => 'Ensure editorial quality and prepare content for human approval.'],
            ['employee_code' => 'MARTIN-NIKOLOVSKI', 'full_name' => 'Martin Nikolovski', 'department' => 'Research', 'role_title' => 'Researcher AI', 'mission' => 'Find reliable sources, extract facts, and prepare research packages.'],
            ['employee_code' => 'MILA-ANDONOVA', 'full_name' => 'Mila Andonova', 'department' => 'Writing', 'role_title' => 'Macedonian Writer AI', 'mission' => 'Write original Macedonian articles based on approved research.'],
            ['employee_code' => 'SARA-ILIEVA', 'full_name' => 'Sara Ilieva', 'department' => 'Localization', 'role_title' => 'Translator / Localization AI', 'mission' => 'Convert foreign-language information into natural Macedonian context.'],
            ['employee_code' => 'VIKTOR-PETROV', 'full_name' => 'Viktor Petrov', 'department' => 'SEO', 'role_title' => 'SEO AI', 'mission' => 'Optimize content for search, discovery, CTR, and structured metadata.'],
            ['employee_code' => 'DAVID-KOSTOVSKI', 'full_name' => 'David Kostovski', 'department' => 'Trust & Safety', 'role_title' => 'Fact Checker AI', 'mission' => 'Verify claims, detect unsupported statements, and flag risk.'],
        ];

        foreach ($employees as $employee) {
            $department = Department::query()
                ->where('organization_id', $organization->id)
                ->where('name', $employee['department'])
                ->firstOrFail();

            Employee::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'employee_code' => $employee['employee_code'],
                ],
                [
                    'department_id' => $department->id,
                    'full_name' => $employee['full_name'],
                    'slug' => Str::slug($employee['full_name']),
                    'role_title' => $employee['role_title'],
                    'employment_status' => 'active',
                    'bio' => $employee['mission'],
                    'job_description' => $employee['mission'],
                    'mission' => $employee['mission'],
                    'responsibilities' => [$employee['mission']],
                    'languages' => ['mk', 'en'],
                    'communication_style' => 'Clear, structured, and review-oriented.',
                    'personality_profile' => ['tone' => 'professional'],
                    'approval_authority_level' => 'none',
                    'metadata' => [],
                    'hired_at' => now(),
                ],
            );
        }
    }
}
