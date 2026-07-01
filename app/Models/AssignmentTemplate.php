<?php

namespace App\Models;

use Database\Factories\AssignmentTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentTemplate extends Model
{
    /** @use HasFactory<AssignmentTemplateFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'briefing_template' => 'array',
            'input_mapping' => 'array',
            'review_required' => 'boolean',
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

    public function businessProcessStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessStep::class);
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

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }
}
