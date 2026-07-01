<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_process_definition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_process_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_process_run_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event_type');
            $table->string('event_key')->nullable();
            $table->text('summary');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['organization_id', 'occurred_at'], 'pe_org_occurred_idx');
            $table->index(['organization_id', 'event_type'], 'pe_org_event_type_idx');
            $table->index(['business_process_run_id', 'occurred_at'], 'pe_run_occurred_idx');
            $table->index(['business_process_run_step_id', 'occurred_at'], 'pe_step_occurred_idx');
            $table->index(['assignment_id', 'occurred_at'], 'pe_assignment_occurred_idx');
            $table->index(['actor_type', 'actor_id'], 'pe_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_events');
    }
};
