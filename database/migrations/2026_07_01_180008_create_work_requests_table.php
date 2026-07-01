<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_definition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_process_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_process_run_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('standard_operating_procedure_id')->nullable()->constrained('standard_operating_procedures')->nullOnDelete();
            $table->foreignId('required_capability_id')->nullable()->constrained('capabilities')->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('work_request_key')->nullable();
            $table->string('title');
            $table->string('assignment_type');
            $table->string('priority');
            $table->string('status');
            $table->string('routing_strategy');
            $table->json('briefing');
            $table->text('expected_output')->nullable();
            $table->json('input_payload')->nullable();
            $table->boolean('review_required')->default(false);
            $table->text('review_path')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('blocked_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('routing_started_at')->nullable();
            $table->timestamp('routed_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'work_request_key'], 'wr_org_key_unique');
            $table->index(['organization_id', 'status'], 'wr_org_status_idx');
            $table->index(['organization_id', 'department_id', 'status'], 'wr_org_department_status_idx');
            $table->index(['organization_id', 'site_id', 'status'], 'wr_org_site_status_idx');
            $table->index(['business_process_run_id', 'status'], 'wr_run_status_idx');
            $table->index('business_process_run_step_id', 'wr_run_step_idx');
            $table->index('assignment_template_id', 'wr_assignment_template_idx');
            $table->index('assignment_id', 'wr_assignment_idx');
            $table->index('requested_at', 'wr_requested_at_idx');
            $table->index('due_at', 'wr_due_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_requests');
    }
};
