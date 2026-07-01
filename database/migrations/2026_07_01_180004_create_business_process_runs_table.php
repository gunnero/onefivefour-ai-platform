<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_process_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('started_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('current_run_step_id')->nullable();
            $table->string('run_key')->nullable();
            $table->string('title');
            $table->string('status');
            $table->string('priority');
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'run_key'], 'bpr_org_run_key_unique');
            $table->index(['organization_id', 'status'], 'bpr_org_status_idx');
            $table->index(['organization_id', 'business_process_definition_id'], 'bpr_org_definition_idx');
            $table->index(['organization_id', 'site_id', 'status'], 'bpr_org_site_status_idx');
            $table->index('current_run_step_id', 'bpr_current_step_idx');
            $table->index('started_at', 'bpr_started_at_idx');
            $table->index('completed_at', 'bpr_completed_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_process_runs');
    }
};
