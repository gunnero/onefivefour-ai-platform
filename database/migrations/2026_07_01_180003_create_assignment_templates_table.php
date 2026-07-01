<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_operating_procedure_id')->nullable()->constrained('standard_operating_procedures')->nullOnDelete();
            $table->foreignId('required_capability_id')->nullable()->constrained('capabilities')->nullOnDelete();
            $table->string('template_key');
            $table->string('title_template');
            $table->string('assignment_type');
            $table->string('priority');
            $table->json('briefing_template');
            $table->text('expected_output')->nullable();
            $table->json('input_mapping')->nullable();
            $table->boolean('review_required')->default(false);
            $table->text('review_path')->nullable();
            $table->unsignedInteger('due_offset_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['business_process_step_id', 'template_key'], 'at_step_key_unique');
            $table->index(['organization_id', 'business_process_definition_id'], 'at_org_definition_idx');
            $table->index(['organization_id', 'business_process_step_id'], 'at_org_step_idx');
            $table->index('department_id', 'at_department_idx');
            $table->index('required_capability_id', 'at_required_capability_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_templates');
    }
};
