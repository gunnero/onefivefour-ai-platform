<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_operating_procedure_id')->nullable()->constrained('standard_operating_procedures')->nullOnDelete();
            $table->string('title');
            $table->string('assignment_type');
            $table->string('priority');
            $table->string('status');
            $table->json('briefing');
            $table->text('expected_output')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('required_capability_keys')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->boolean('escalation_required')->default(false);
            $table->boolean('review_required')->default(false);
            $table->text('review_path')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'employee_id', 'status']);
            $table->index(['organization_id', 'department_id', 'status'], 'assignments_org_department_status_idx');
            $table->index(['organization_id', 'site_id', 'status']);
            $table->index('standard_operating_procedure_id', 'assignments_sop_idx');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
