<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'languages' => 'array',
            'personality_profile' => 'array',
            'metadata' => 'array',
            'hired_at' => 'datetime',
            'paused_at' => 'datetime',
            'retired_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_employee_id');
    }

    public function managedEmployees(): HasMany
    {
        return $this->hasMany(self::class, 'manager_employee_id');
    }

    public function employeeCapabilities(): HasMany
    {
        return $this->hasMany(EmployeeCapability::class);
    }

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(Capability::class, 'employee_capabilities')
            ->using(EmployeeCapability::class)
            ->withPivot(['organization_id', 'status', 'level', 'notes', 'granted_at', 'revoked_at'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
