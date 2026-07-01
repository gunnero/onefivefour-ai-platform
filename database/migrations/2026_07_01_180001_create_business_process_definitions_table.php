<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_process_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owning_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('process_key');
            $table->string('name');
            $table->string('status');
            $table->unsignedInteger('version');
            $table->text('purpose');
            $table->text('trigger_description')->nullable();
            $table->json('input_schema')->nullable();
            $table->json('completion_criteria')->nullable();
            $table->boolean('default_site_required')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'process_key', 'version'], 'bpd_org_key_version_unique');
            $table->index(['organization_id', 'status'], 'bpd_org_status_idx');
            $table->index(['organization_id', 'process_key'], 'bpd_org_key_idx');
            $table->index('owning_department_id', 'bpd_owning_department_idx');
            $table->index('manager_employee_id', 'bpd_manager_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_process_definitions');
    }
};
