<?php

namespace App\Models;

use Database\Factories\BusinessProcessRunStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProcessRunStep extends Model
{
    /** @use HasFactory<BusinessProcessRunStepFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'approval_required' => 'boolean',
            'input_payload' => 'array',
            'output_payload' => 'array',
            'ready_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'blocked_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function businessProcessRun(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRun::class);
    }

    public function businessProcessStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessStep::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function processEvents(): HasMany
    {
        return $this->hasMany(ProcessEvent::class);
    }

    public function processLogs(): HasMany
    {
        return $this->hasMany(ProcessLog::class);
    }
}
