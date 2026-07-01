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
        Schema::create('sop_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('standard_operating_procedure_id')->constrained('standard_operating_procedures')->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained()->cascadeOnDelete();
            $table->string('required_level')->nullable();
            $table->timestamps();

            $table->unique(['standard_operating_procedure_id', 'capability_id'], 'sop_capabilities_sop_capability_unique');
            $table->index(['organization_id', 'standard_operating_procedure_id'], 'sop_capabilities_org_sop_idx');
            $table->index(['organization_id', 'capability_id'], 'sop_capabilities_org_capability_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_capabilities');
    }
};
