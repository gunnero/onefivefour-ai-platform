<?php

namespace App\Models;

use Database\Factories\ProcessLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessLog extends Model
{
    /** @use HasFactory<ProcessLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
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

    public function businessProcessRunStep(): BelongsTo
    {
        return $this->belongsTo(BusinessProcessRunStep::class);
    }

    public function processEvent(): BelongsTo
    {
        return $this->belongsTo(ProcessEvent::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }
}
