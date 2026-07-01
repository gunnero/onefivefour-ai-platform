<?php

namespace App\Models;

use Database\Factories\DepartmentQueueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentQueue extends Model
{
    /** @use HasFactory<DepartmentQueueFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'routing_paused_until' => 'datetime',
            'metadata' => 'array',
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function lastSelectedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'last_selected_employee_id');
    }
}
