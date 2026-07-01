<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('business_process_run_id')->nullable()->after('standard_operating_procedure_id')->constrained()->nullOnDelete();
            $table->foreignId('business_process_run_step_id')->nullable()->after('business_process_run_id')->constrained()->nullOnDelete();
            $table->foreignId('work_request_id')->nullable()->after('business_process_run_step_id')->constrained()->nullOnDelete();
            $table->foreignId('routing_decision_id')->nullable()->after('work_request_id')->constrained()->nullOnDelete();

            $table->index('business_process_run_id', 'assignments_process_run_idx');
            $table->index('business_process_run_step_id', 'assignments_run_step_idx');
            $table->index('work_request_id', 'assignments_work_request_idx');
            $table->index('routing_decision_id', 'assignments_routing_decision_idx');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['business_process_run_id']);
            $table->dropForeign(['business_process_run_step_id']);
            $table->dropForeign(['work_request_id']);
            $table->dropForeign(['routing_decision_id']);
            $table->dropIndex('assignments_process_run_idx');
            $table->dropIndex('assignments_run_step_idx');
            $table->dropIndex('assignments_work_request_idx');
            $table->dropIndex('assignments_routing_decision_idx');
            $table->dropColumn([
                'business_process_run_id',
                'business_process_run_step_id',
                'work_request_id',
                'routing_decision_id',
            ]);
        });
    }
};
