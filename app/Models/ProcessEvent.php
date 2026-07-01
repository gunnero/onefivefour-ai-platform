<?php

namespace App\Models;

use Database\Factories\ProcessEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessEvent extends Model
{
    /** @use HasFactory<ProcessEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
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

    public function businessProcessRun(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRun::class);
    }

    public function businessProcessRunStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRunStep::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function processLogs(): HasMany
    {
        return $this->hasMany(ProcessLog::class);
    }
}
