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
        Schema::create('standard_operating_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sop_key');
            $table->string('title');
            $table->string('status');
            $table->text('purpose');
            $table->text('trigger_description')->nullable();
            $table->json('inputs_schema')->nullable();
            $table->json('steps');
            $table->json('success_criteria')->nullable();
            $table->json('quality_checks')->nullable();
            $table->json('escalation_rules')->nullable();
            $table->json('output_expectations')->nullable();
            $table->unsignedInteger('version');
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'sop_key', 'version']);
            $table->index(['organization_id', 'department_id'], 'sops_org_department_idx');
            $table->index(['organization_id', 'status'], 'sops_org_status_idx');
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_operating_procedures');
    }
};
