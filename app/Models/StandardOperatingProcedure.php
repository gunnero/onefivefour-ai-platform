<?php

namespace App\Models;

use Database\Factories\StandardOperatingProcedureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardOperatingProcedure extends Model
{
    /** @use HasFactory<StandardOperatingProcedureFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'inputs_schema' => 'array',
            'steps' => 'array',
            'success_criteria' => 'array',
            'quality_checks' => 'array',
            'escalation_rules' => 'array',
            'output_expectations' => 'array',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function sopPolicies(): HasMany
    {
        return $this->hasMany(SopPolicy::class);
    }

    public function sopCapabilities(): HasMany
    {
        return $this->hasMany(SopCapability::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class, 'sop_policies')
            ->using(SopPolicy::class)
            ->withPivot(['organization_id'])
            ->withTimestamps();
    }

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(Capability::class, 'sop_capabilities')
            ->using(SopCapability::class)
            ->withPivot(['organization_id', 'required_level'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function businessProcessSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessStep::class);
    }

    public function assignmentTemplates(): HasMany
    {
        return $this->hasMany(AssignmentTemplate::class);
    }

    public function businessProcessRunSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessRunStep::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }
}
