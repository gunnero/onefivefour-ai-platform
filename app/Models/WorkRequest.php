<?php

namespace App\Models;

use Database\Factories\WorkRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkRequest extends Model
{
    /** @use HasFactory<WorkRequestFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'briefing' => 'array',
            'input_payload' => 'array',
            'review_required' => 'boolean',
            'due_at' => 'datetime',
            'requested_at' => 'datetime',
            'routing_started_at' => 'datetime',
            'routed_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'blocked_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function businessProcessDefinition(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessDefinition::class);
    }

    public function businessProcessRun(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRun::class);
    }

    public function businessProcessRunStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRunStep::class);
    }

    public function assignmentTemplate(): BelongsTo
    {
        return $this->belongsTo(AssignmentTemplate::class);
    }

    public function standardOperatingProcedure(): BelongsTo
    {
        return $this->belongsTo(StandardOperatingProcedure::class);
    }

    public function requiredCapability(): BelongsTo
    {
        return $this->belongsTo(Capability::class, 'required_capability_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function routingDecisions(): HasMany
    {
        return $this->hasMany(RoutingDecision::class);
    }
}
