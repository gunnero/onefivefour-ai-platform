<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_run_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('process_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('log_level');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'created_at'], 'pl_org_created_idx');
            $table->index(['business_process_run_id', 'created_at'], 'pl_run_created_idx');
            $table->index(['business_process_run_step_id', 'created_at'], 'pl_step_created_idx');
            $table->index('process_event_id', 'pl_event_idx');
            $table->index('assignment_id', 'pl_assignment_idx');
            $table->index('log_level', 'pl_log_level_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_logs');
    }
};
