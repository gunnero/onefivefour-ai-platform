<?php

namespace App\Models;

use Database\Factories\CapabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capability extends Model
{
    /** @use HasFactory<CapabilityFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function employeeCapabilities(): HasMany
    {
        return $this->hasMany(EmployeeCapability::class);
    }

    public function sopCapabilities(): HasMany
    {
        return $this->hasMany(SopCapability::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_capabilities')
            ->using(EmployeeCapability::class)
            ->withPivot(['organization_id', 'status', 'level', 'notes', 'granted_at', 'revoked_at'])
            ->withTimestamps();
    }

    public function standardOperatingProcedures(): BelongsToMany
    {
        return $this->belongsToMany(StandardOperatingProcedure::class, 'sop_capabilities')
            ->using(SopCapability::class)
            ->withPivot(['organization_id', 'required_level'])
            ->withTimestamps();
    }
}
