<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routing_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('selected_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('strategy');
            $table->string('status');
            $table->unsignedInteger('candidate_count')->default(0);
            $table->unsignedInteger('eligible_count')->default(0);
            $table->json('candidate_snapshot')->nullable();
            $table->json('eligibility_results')->nullable();
            $table->text('decision_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->boolean('manager_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->string('decided_by_type')->nullable();
            $table->unsignedBigInteger('decided_by_id')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status'], 'rd_org_status_idx');
            $table->index(['work_request_id', 'created_at'], 'rd_work_request_created_idx');
            $table->index(['organization_id', 'department_id', 'status'], 'rd_org_department_status_idx');
            $table->index('selected_employee_id', 'rd_selected_employee_idx');
            $table->index('assignment_id', 'rd_assignment_idx');
            $table->index('decided_at', 'rd_decided_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_decisions');
    }
};
