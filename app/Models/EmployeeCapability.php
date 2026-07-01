<?php

namespace App\Models;

use Database\Factories\EmployeeCapabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeeCapability extends Pivot
{
    /** @use HasFactory<EmployeeCapabilityFactory> */
    use HasFactory;

    protected $table = 'employee_capabilities';

    protected $guarded = [];

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(Capability::class);
    }
}
