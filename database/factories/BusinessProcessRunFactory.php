<?php

namespace Database\Factories;

use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\Organization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProcessRun>
 */
class BusinessProcessRunFactory extends Factory
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
            'business_process_definition_id' => BusinessProcessDefinition::factory(),
            'site_id' => Site::factory(),
            'started_by_user_id' => null,
            'current_run_step_id' => null,
            'run_key' => $this->faker->unique()->uuid(),
            'title' => $this->faker->sentence(4),
            'status' => 'pending',
            'priority' => 'normal',
            'input_payload' => ['source' => 'factory'],
            'output_payload' => null,
            'progress_percent' => 0,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'failed_at' => null,
            'blocked_at' => null,
            'metadata' => [],
        ];
    }
}
