<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
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
            'actor_type' => 'system',
            'actor_id' => null,
            'auditable_type' => 'system',
            'auditable_id' => 0,
            'event_type' => 'record_created',
            'action' => 'created',
            'summary' => $this->faker->sentence(),
            'before_state' => null,
            'after_state' => [],
            'reason' => null,
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
