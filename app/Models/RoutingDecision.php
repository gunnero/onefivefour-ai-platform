<?php

namespace App\Models;

use Database\Factories\RoutingDecisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingDecision extends Model
{
    /** @use HasFactory<RoutingDecisionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'candidate_snapshot' => 'array',
            'eligibility_results' => 'array',
            'manager_override' => 'boolean',
            'decided_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workRequest(): BelongsTo
    {
        return $this->belongsTo(WorkRequest::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function selectedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'selected_employee_id');
    }
}
