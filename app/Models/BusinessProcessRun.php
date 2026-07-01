<?php

namespace App\Models;

use Database\Factories\BusinessProcessRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProcessRun extends Model
{
    /** @use HasFactory<BusinessProcessRunFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'failed_at' => 'datetime',
            'blocked_at' => 'datetime',
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function currentRunStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRunStep::class, 'current_run_step_id');
    }

    public function runSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessRunStep::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
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
