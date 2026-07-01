<?php

namespace Database\Factories;

use App\Models\AssignmentTemplate;
use App\Models\BusinessProcessDefinition;
use App\Models\BusinessProcessStep;
use App\Models\Capability;
use App\Models\Department;
use App\Models\Organization;
use App\Models\StandardOperatingProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AssignmentTemplate>
 */
class AssignmentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'organization_id' => Organization::factory(),
            'business_process_definition_id' => BusinessProcessDefinition::factory(),
            'business_process_step_id' => BusinessProcessStep::factory(),
            'department_id' => Department::factory(),
            'standard_operating_procedure_id' => StandardOperatingProcedure::factory(),
            'required_capability_id' => Capability::factory(),
            'template_key' => Str::slug($title),
            'title_template' => $title,
            'assignment_type' => 'business_process_step',
            'priority' => 'normal',
            'briefing_template' => ['summary' => $this->faker->sentence()],
            'expected_output' => $this->faker->sentence(),
            'input_mapping' => ['source' => 'process_run_step'],
            'review_required' => false,
            'review_path' => null,
            'due_offset_minutes' => 1440,
            'metadata' => [],
        ];
    }
}
