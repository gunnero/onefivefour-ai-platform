<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
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

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function standardOperatingProcedures(): HasMany
    {
        return $this->hasMany(StandardOperatingProcedure::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function ownedBusinessProcessDefinitions(): HasMany
    {
        return $this->hasMany(BusinessProcessDefinition::class, 'owning_department_id');
    }

    public function businessProcessSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessStep::class);
    }

    public function assignmentTemplates(): HasMany
    {
        return $this->hasMany(AssignmentTemplate::class);
    }

    public function businessProcessRunSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessRunStep::class);
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
