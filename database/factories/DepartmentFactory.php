<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
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
            'parent_department_id' => null,
            'manager_employee_id' => null,
            'name' => $this->faker->unique()->words(2, true),
            'slug' => $this->faker->unique()->slug(2),
            'status' => 'active',
            'purpose' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 50),
            'metadata' => [],
        ];
    }
}
