<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_operating_procedure_id')->nullable()->constrained('standard_operating_procedures')->nullOnDelete();
            $table->foreignId('required_capability_id')->nullable()->constrained('capabilities')->nullOnDelete();
            $table->string('step_key');
            $table->string('name');
            $table->string('status');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->text('expected_output')->nullable();
            $table->json('dependency_rules')->nullable();
            $table->boolean('approval_required')->default(false);
            $table->json('approval_rule')->nullable();
            $table->json('retry_rule')->nullable();
            $table->json('failure_rule')->nullable();
            $table->json('escalation_rule')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['business_process_definition_id', 'step_key'], 'bps_definition_key_unique');
            $table->index(['organization_id', 'business_process_definition_id'], 'bps_org_definition_idx');
            $table->index(['organization_id', 'department_id'], 'bps_org_department_idx');
            $table->index('required_capability_id', 'bps_required_capability_idx');
            $table->index('standard_operating_procedure_id', 'bps_sop_idx');
            $table->index('status', 'bps_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_process_steps');
    }
};
