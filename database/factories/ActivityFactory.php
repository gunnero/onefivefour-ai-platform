<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'site_id' => Site::factory(),
            'department_id' => Department::factory(),
            'employee_id' => Employee::factory(),
            'assignment_id' => Assignment::factory(),
            'audit_log_id' => AuditLog::factory(),
            'activity_type' => 'assignment_created',
            'status' => 'visible',
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->sentence(),
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
