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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('audit_log_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type');
            $table->string('status');
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['organization_id', 'occurred_at']);
            $table->index(['organization_id', 'activity_type']);
            $table->index(['organization_id', 'status']);
            $table->index(['site_id', 'occurred_at']);
            $table->index(['department_id', 'occurred_at']);
            $table->index(['employee_id', 'occurred_at']);
            $table->index(['assignment_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
