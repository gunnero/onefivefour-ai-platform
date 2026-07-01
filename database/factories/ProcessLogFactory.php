<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\Organization;
use App\Models\ProcessEvent;
use App\Models\ProcessLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProcessLog>
 */
class ProcessLogFactory extends Factory
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
            'business_process_run_id' => BusinessProcessRun::factory(),
            'business_process_run_step_id' => BusinessProcessRunStep::factory(),
            'process_event_id' => ProcessEvent::factory(),
            'assignment_id' => Assignment::factory(),
            'log_level' => 'info',
            'message' => $this->faker->sentence(),
            'context' => ['source' => 'factory'],
            'created_at' => now(),
        ];
    }
}
