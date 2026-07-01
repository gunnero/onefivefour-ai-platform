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
        Schema::create('policy_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->timestamps();

            $table->unique(['policy_id', 'scope_type', 'scope_id']);
            $table->index(['organization_id', 'scope_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_scopes');
    }
};
