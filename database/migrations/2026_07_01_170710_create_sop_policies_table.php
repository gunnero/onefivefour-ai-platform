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
        Schema::create('sop_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_operating_procedure_id')->constrained('standard_operating_procedures')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['standard_operating_procedure_id', 'policy_id'], 'sop_policies_sop_policy_unique');
            $table->index(['organization_id', 'standard_operating_procedure_id'], 'sop_policies_org_sop_idx');
            $table->index(['organization_id', 'policy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_policies');
    }
};
