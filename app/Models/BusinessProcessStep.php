<?php

namespace App\Models;

use Database\Factories\BusinessProcessStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProcessStep extends Model
{
    /** @use HasFactory<BusinessProcessStepFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dependency_rules' => 'array',
            'approval_required' => 'boolean',
            'approval_rule' => 'array',
            'retry_rule' => 'array',
            'failure_rule' => 'array',
            'escalation_rule' => 'array',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function businessProcessDefinition(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessDefinition::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function standardOperatingProcedure(): BelongsTo
    {
        return $this->belongsTo(StandardOperatingProcedure::class);
    }

    public function requiredCapability(): BelongsTo
    {
        return $this->belongsTo(Capability::class, 'required_capability_id');
    }

    public function assignmentTemplates(): HasMany
    {
        return $this->hasMany(AssignmentTemplate::class);
    }

    public function runSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessRunStep::class);
    }
}
