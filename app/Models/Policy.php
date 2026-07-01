<?php

namespace App\Models;

use Database\Factories\PolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    /** @use HasFactory<PolicyFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(PolicyScope::class);
    }

    public function sopPolicies(): HasMany
    {
        return $this->hasMany(SopPolicy::class);
    }

    public function standardOperatingProcedures(): BelongsToMany
    {
        return $this->belongsToMany(StandardOperatingProcedure::class, 'sop_policies')
            ->using(SopPolicy::class)
            ->withPivot(['organization_id'])
            ->withTimestamps();
    }
}
