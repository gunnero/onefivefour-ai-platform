<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\DepartmentQueue;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DepartmentQueue>
 */
class DepartmentQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'organization_id' => Organization::factory(),
            'department_id' => Department::factory(),
            'site_id' => Site::factory(),
            'queue_key' => Str::slug($name).'-queue',
            'name' => Str::title($name).' Queue',
            'status' => 'active',
            'default_routing_strategy' => 'first_available',
            'max_active_assignments_per_employee' => 3,
            'pending_work_request_count' => 0,
            'blocked_work_request_count' => 0,
            'failed_work_request_count' => 0,
            'last_selected_employee_id' => Employee::factory(),
            'routing_paused_reason' => null,
            'routing_paused_until' => null,
            'metadata' => [],
        ];
    }
}
