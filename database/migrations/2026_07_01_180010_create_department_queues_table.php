<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('queue_key');
            $table->string('name');
            $table->string('status');
            $table->string('default_routing_strategy');
            $table->unsignedInteger('max_active_assignments_per_employee')->nullable();
            $table->unsignedInteger('pending_work_request_count')->default(0);
            $table->unsignedInteger('blocked_work_request_count')->default(0);
            $table->unsignedInteger('failed_work_request_count')->default(0);
            $table->foreignId('last_selected_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('routing_paused_reason')->nullable();
            $table->timestamp('routing_paused_until')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'queue_key'], 'dq_org_key_unique');
            $table->unique(['organization_id', 'department_id', 'site_id'], 'dq_org_department_site_unique');
            $table->index(['organization_id', 'status'], 'dq_org_status_idx');
            $table->index(['organization_id', 'department_id'], 'dq_org_department_idx');
            $table->index('site_id', 'dq_site_idx');
            $table->index('last_selected_employee_id', 'dq_last_selected_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_queues');
    }
};
