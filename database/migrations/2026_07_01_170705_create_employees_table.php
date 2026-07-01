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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_code');
            $table->string('full_name');
            $table->string('slug');
            $table->string('role_title');
            $table->string('employment_status')->index();
            $table->string('avatar_url')->nullable();
            $table->text('bio')->nullable();
            $table->text('job_description')->nullable();
            $table->text('mission')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('languages')->nullable();
            $table->text('communication_style')->nullable();
            $table->json('personality_profile')->nullable();
            $table->string('approval_authority_level')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('hired_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'employee_code']);
            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
