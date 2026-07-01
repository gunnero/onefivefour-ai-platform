<?php

namespace App\Models;

use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function standardOperatingProcedures(): HasMany
    {
        return $this->hasMany(StandardOperatingProcedure::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function businessProcessRuns(): HasMany
    {
        return $this->hasMany(BusinessProcessRun::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function routingDecisions(): HasMany
    {
        return $this->hasMany(RoutingDecision::class);
    }

    public function departmentQueues(): HasMany
    {
        return $this->hasMany(DepartmentQueue::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
