<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'department_id' => Department::factory(),
            'manager_employee_id' => null,
            'employee_code' => strtoupper($this->faker->unique()->bothify('EMP-###??')),
            'full_name' => $this->faker->name(),
            'slug' => $this->faker->unique()->slug(2),
            'role_title' => $this->faker->jobTitle(),
            'employment_status' => 'active',
            'avatar_url' => null,
            'bio' => $this->faker->paragraph(),
            'job_description' => $this->faker->paragraph(),
            'mission' => $this->faker->sentence(),
            'responsibilities' => [$this->faker->sentence()],
            'languages' => ['en'],
            'communication_style' => 'Clear and concise.',
            'personality_profile' => ['tone' => 'professional'],
            'approval_authority_level' => 'none',
            'metadata' => [],
            'hired_at' => now(),
            'paused_at' => null,
            'retired_at' => null,
            'archived_at' => null,
        ];
    }
}
