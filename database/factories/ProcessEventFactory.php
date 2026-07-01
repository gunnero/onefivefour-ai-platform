<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessRun;
use App\Models\BusinessProcessRunStep;
use App\Models\Organization;
use App\Models\ProcessEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProcessEvent>
 */
class ProcessEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = 'process.created';

        return [
            'organization_id' => Organization::factory(),
            'business_process_definition_id' => BusinessProcessDefinition::factory(),
            'business_process_run_id' => BusinessProcessRun::factory(),
            'business_process_run_step_id' => BusinessProcessRunStep::factory(),
            'assignment_id' => Assignment::factory(),
            'actor_type' => null,
            'actor_id' => null,
            'event_type' => $eventType,
            'event_key' => Str::uuid()->toString(),
            'summary' => $this->faker->sentence(),
            'payload' => ['source' => 'factory'],
            'occurred_at' => now(),
            'created_at' => now(),
        ];
    }
}
