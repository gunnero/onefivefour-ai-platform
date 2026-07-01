<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_process_run_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('standard_operating_procedure_id')->nullable()->constrained('standard_operating_procedures')->nullOnDelete();
            $table->foreignId('required_capability_id')->nullable()->constrained('capabilities')->nullOnDelete();
            $table->string('status');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('attempt_number')->default(1);
            $table->boolean('approval_required')->default(false);
            $table->string('approval_status')->nullable();
            $table->text('blocked_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['business_process_run_id', 'business_process_step_id', 'attempt_number'], 'bprs_run_step_attempt_unique');
            $table->index(['organization_id', 'business_process_run_id', 'status'], 'bprs_org_run_status_idx');
            $table->index(['organization_id', 'department_id', 'status'], 'bprs_org_department_status_idx');
            $table->index(['organization_id', 'employee_id', 'status'], 'bprs_org_employee_status_idx');
            $table->index('assignment_id', 'bprs_assignment_idx');
            $table->index('ready_at', 'bprs_ready_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_process_run_steps');
    }
};
